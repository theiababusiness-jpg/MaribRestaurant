import fs from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

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

    const pipeline = sharp(input)
        .rotate()
        .resize({
            width: maxWidth,
            height: maxHeight,
            fit: 'inside',
            withoutEnlargement: true,
            kernel: sharp.kernel.lanczos3,
        })
        .webp({
            quality,
            alphaQuality: 100,
            effort: 4,
            exact: true,
        });

    const info = await pipeline.toFile(output);
    process.stdout.write(JSON.stringify({
        ok: true,
        ...info,
    }));
}

main().catch((error) => {
    process.stderr.write(error instanceof Error ? error.stack ?? error.message : String(error));
    process.exitCode = 1;
});
