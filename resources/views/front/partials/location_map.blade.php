@php
    use App\Support\FrontLang;

    $embedUrl = $embedUrl ?? 'https://www.google.com/maps?q=26.44908077907955,50.09990773653787&z=16&output=embed';
    $openUrl = $openUrl ?? 'https://www.google.com/maps?q=26.44908077907955,50.09990773653787';
    $openLabel = $openLabel ?? FrontLang::t('افتح الموقع على الخريطة', 'Open location on map');
    $wrapperClass = trim(($wrapperClass ?? 'about-map') . ' shared-location-map');
    $iframeId = $iframeId ?? null;
    $linkId = $linkId ?? null;
@endphp

@once
    <style>
        .shared-location-map{
            margin-top:60px;
        }

        .shared-location-map__frame{
            position:relative;
            width:100%;
            padding-top:56.25%;
            border-radius:var(--radius);
            overflow:hidden;
            box-shadow:var(--shadow);
            border:1px solid var(--border);
            background:var(--card);
        }

        .shared-location-map__frame iframe{
            position:absolute;
            inset:0;
            width:100%;
            height:100%;
            border:0;
        }

        .shared-location-map__actions{
            margin-top:16px;
        }
    </style>
@endonce

<div class="{{ $wrapperClass }}" data-shared-location-map="1">
    <div class="map-frame-responsive shared-location-map__frame">
        <iframe
            @if($iframeId) id="{{ $iframeId }}" @endif
            src="{{ $embedUrl }}"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    @if(filled($openUrl))
        <div class="about-actions shared-location-map__actions">
            <a
                class="btn dark-btn"
                @if($linkId) id="{{ $linkId }}" @endif
                target="_blank"
                rel="noopener"
                href="{{ $openUrl }}">
                {{ $openLabel }}
            </a>
        </div>
    @endif
</div>
