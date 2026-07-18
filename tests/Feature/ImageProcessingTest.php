<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ProductController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Services\Images\ImageProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $temporaryPaths = [];

    protected function tearDown(): void
    {
        foreach (array_unique($this->temporaryPaths) as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_service_converts_images_to_webp_without_upscaling_small_sources(): void
    {
        $service = app(ImageProcessingService::class);
        $sourcePath = $this->createSourceImage(80, 50, 'png');
        $upload = new UploadedFile($sourcePath, 'source.png', 'image/png', null, true);

        $storedPath = $service->store($upload, 'images/testing');
        $absolutePath = public_path($storedPath);
        $this->trackTemporaryPath($absolutePath);

        $this->assertFileExists($absolutePath);
        $this->assertTrue(str_ends_with($storedPath, '.webp'));
        $this->assertSame('image/webp', mime_content_type($absolutePath));

        [$width, $height] = getimagesize($absolutePath);
        $this->assertSame(80, $width);
        $this->assertSame(50, $height);
    }

    public function test_service_falls_back_to_the_secondary_processor_when_the_primary_script_is_unavailable(): void
    {
        $service = app(ImageProcessingService::class);
        $sourcePath = $this->createSourceImage(1800, 1200, 'jpeg');
        $upload = new UploadedFile($sourcePath, 'fallback.jpg', 'image/jpeg', null, true);

        $originalScriptPath = config('image-processing.script_path');
        $originalFallbackPath = config('image-processing.fallback_script_path');

        config([
            'image-processing.script_path' => base_path('scripts/does-not-exist.mjs'),
            'image-processing.fallback_script_path' => $originalFallbackPath,
        ]);

        try {
            $storedPath = $service->store($upload, 'images/testing-fallback');
        } finally {
            config([
                'image-processing.script_path' => $originalScriptPath,
                'image-processing.fallback_script_path' => $originalFallbackPath,
            ]);
        }

        $absolutePath = public_path($storedPath);
        $this->trackTemporaryPath($absolutePath);

        $this->assertFileExists($absolutePath);
        $this->assertTrue(str_ends_with($storedPath, '.webp'));
        $this->assertSame('image/webp', mime_content_type($absolutePath));

        [$width, $height] = getimagesize($absolutePath);
        $this->assertSame(1400, $width);
        $this->assertSame(933, $height);
    }

    public function test_product_store_and_update_use_the_shared_image_pipeline(): void
    {
        $this->withoutMiddleware();

        $category = Category::create([
            'name' => 'Main Dishes',
            'description' => null,
            'slug' => 'main-dishes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $storeImage = $this->createUploadedImage(2200, 1400, 'jpeg', 'product-store.jpg');
        $storeResponse = $this->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Grilled Chicken',
            'price' => 45,
            'is_active' => 1,
            'sort_order' => 0,
            'image' => $storeImage,
        ]);

        $storeResponse->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Grilled Chicken')->firstOrFail();
        $this->assertNotNull($product->image_path);
        $this->assertTrue(str_ends_with($product->image_path, '.webp'));

        $storedAbsolute = public_path($product->image_path);
        $this->trackTemporaryPath($storedAbsolute);
        $this->assertFileExists($storedAbsolute);
        $this->assertSame('image/webp', mime_content_type($storedAbsolute));

        [$width, $height] = getimagesize($storedAbsolute);
        $this->assertSame(1400, $width);
        $this->assertSame(891, $height);

        $oldImagePath = $product->image_path;

        $updateImage = $this->createUploadedImage(3000, 2000, 'jpeg', 'product-update.jpg');
        $updateRequest = Request::create(route('admin.products.update', $product), 'POST', [
            'category_id' => $category->id,
            'name' => 'Grilled Chicken Deluxe',
            'price' => 52,
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        $updateRequest->files->set('image', $updateImage);
        $updateRequest->setMethod('PUT');

        $updateResponse = app(ProductController::class)->update(
            $updateRequest,
            $product,
            app(ImageProcessingService::class)
        );

        $this->assertTrue($updateResponse->isRedirect());
        $this->assertSame(route('admin.products.index'), $updateResponse->getTargetUrl());

        $product->refresh();

        $this->assertNotSame($oldImagePath, $product->image_path);
        $this->assertFileDoesNotExist(public_path($oldImagePath));
        $updatedAbsolute = public_path($product->image_path);
        $this->trackTemporaryPath($updatedAbsolute);
        $this->assertFileExists($updatedAbsolute);

        [$updatedWidth, $updatedHeight] = getimagesize($updatedAbsolute);
        $this->assertSame(1400, $updatedWidth);
        $this->assertSame(933, $updatedHeight);
    }

    public function test_banner_store_and_destroy_use_the_shared_image_pipeline(): void
    {
        $this->withoutMiddleware();

        $bannerImage = $this->createUploadedImage(1920, 800, 'png', 'banner.png');

        $storeResponse = $this->post(route('admin.banners.store'), [
            'title' => 'Seasonal Offer',
            'subtitle' => 'Fresh dishes available now',
            'image' => $bannerImage,
            'link_type' => 'none',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $storeResponse->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->where('title', 'Seasonal Offer')->firstOrFail();
        $this->assertTrue(str_ends_with($banner->image_path, '.webp'));
        $bannerPath = public_path($banner->image_path);
        $this->trackTemporaryPath($bannerPath);
        $this->assertFileExists($bannerPath);

        $destroyPath = $banner->image_path;

        app(BannerController::class)->destroy($banner, app(ImageProcessingService::class));

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        $this->assertFileDoesNotExist(public_path($destroyPath));
    }

    private function createUploadedImage(
        int $width,
        int $height,
        string $format,
        string $originalName
    ): UploadedFile {
        $sourcePath = $this->createSourceImage($width, $height, $format);

        return new UploadedFile(
            $sourcePath,
            $originalName,
            $this->guessMimeType($format),
            null,
            true
        );
    }

    private function createSourceImage(int $width, int $height, string $format): string
    {
        $workingDirectory = storage_path('framework/testing');
        $basePath = tempnam($workingDirectory, 'img-');

        if ($basePath === false) {
            $this->fail('Unable to create a temporary image path.');
        }

        @unlink($basePath);

        $scriptPath = $basePath . '.mjs';
        $outputPath = $basePath . '.' . $format;

        file_put_contents($scriptPath, <<<'JS'
import sharp from 'sharp';

const [,, outputPath, widthArg, heightArg, formatArg] = process.argv;
const width = Number.parseInt(widthArg, 10);
const height = Number.parseInt(heightArg, 10);

let pipeline = sharp({
  create: {
    width,
    height,
    channels: 4,
    background: { r: 232, g: 126, b: 58, alpha: 1 },
  },
});

switch (formatArg) {
  case 'png':
    pipeline = pipeline.png();
    break;
  case 'webp':
    pipeline = pipeline.webp({ quality: 90 });
    break;
  default:
    pipeline = pipeline.jpeg({ quality: 92, chromaSubsampling: '4:4:4' });
    break;
}

await pipeline.toFile(outputPath);
JS);

        try {
            $process = new Process(['node', $scriptPath, $outputPath, (string) $width, (string) $height, $format]);
            $process->mustRun();
        } finally {
            @unlink($scriptPath);
        }

        $this->trackTemporaryPath($outputPath);
        $this->trackTemporaryPath($scriptPath);

        return $outputPath;
    }

    private function trackTemporaryPath(string $path): void
    {
        $this->temporaryPaths[] = $path;
    }

    private function guessMimeType(string $format): string
    {
        return match (strtolower($format)) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
