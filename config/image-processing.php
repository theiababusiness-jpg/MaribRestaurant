<?php

return [
    'node_binary' => env('IMAGE_PROCESSOR_NODE_BINARY', 'node'),
    'script_path' => base_path('scripts/image-processor.mjs'),
    'fallback_script_path' => base_path('scripts/image-processor-jimp.mjs'),
    'max_width' => (int) env('IMAGE_PROCESSOR_MAX_WIDTH', 1400),
    'max_height' => (int) env('IMAGE_PROCESSOR_MAX_HEIGHT', 1400),
    'quality' => (int) env('IMAGE_PROCESSOR_WEBP_QUALITY', 82),
    'timeout' => (int) env('IMAGE_PROCESSOR_TIMEOUT', 120),
];
