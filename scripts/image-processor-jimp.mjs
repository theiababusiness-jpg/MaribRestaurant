import fs from 'node:fs/promises';
import path from 'node:path';
import { Jimp } from 'jimp';
import encodeWebp, { init as initWebp } from '@jsquash/webp/encode.js';
import { simd } from 'wasm-feature-detect';

function parseArgs(argv) {
    const result = {};

    for (let index = 0; index < argv.length; index += 2) {
        const key = argv[index];
        const value = argv[index + 1];

        if (!key || !key.startsWith('--')) {
            continue;
        }

        result[key.slice(2)] = value;
    }

    return result;
}

async function main() {
    const args = parseArgs(process.argv.slice(2));
    const input = args.input;
    const output = args.output;

    if (!input || !output) {
        throw new Error('Missing required --input or --output argument.');
    }

    const maxWidth = Number.parseInt(args.maxWidth ?? '1400', 10);
    const maxHeight = Number.parseInt(args.maxHeight ?? '1400', 10);
    const quality = Number.parseInt(args.quality ?? '82', 10);

    await fs.mkdir(path.dirname(output), { recursive: true });

    const image = await Jimp.read(input);
    const width = image.bitmap.width;
    const height = image.bitmap.height;

    if (width > maxWidth || height > maxHeight) {
        image.scaleToFit({ w: maxWidth, h: maxHeight });
    }

    const wasmFile = (await simd())
        ? 'webp_enc_simd.wasm'
        : 'webp_enc.wasm';
    const wasmPath = path.resolve(
        process.cwd(),
        'node_modules/@jsquash/webp/codec/enc',
        wasmFile
    );
    const wasmBinary = await fs.readFile(wasmPath);

    await initWebp({
        wasmBinary,
    });

    const buffer = Buffer.from(await encodeWebp({
        data: new Uint8ClampedArray(image.bitmap.data),
        width: image.bitmap.width,
        height: image.bitmap.height,
    }, {
        quality,
        alpha_quality: 100,
        exact: 1,
    }));

    await fs.writeFile(output, buffer);

    process.stdout.write(JSON.stringify({
        ok: true,
        width: image.bitmap.width,
        height: image.bitmap.height,
        bytes: buffer.length,
    }));
}

main().catch((error) => {
    process.stderr.write(error instanceof Error ? error.stack ?? error.message : String(error));
    process.exitCode = 1;
});
