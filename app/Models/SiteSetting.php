<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_name_en',
        'default_meta_description',
        'default_meta_description_en',
        'home_meta_title',
        'home_meta_title_en',
        'home_meta_description',
        'home_meta_description_en',
        'support_phone',
        'secondary_phone',
        'whatsapp_number',
        'primary_address',
        'primary_address_en',
        'working_hours',
        'working_hours_en',
        'google_maps_url',
        'google_maps_embed_url',
        'facebook_url',
        'instagram_url',
        'x_url',
        'tiktok_url',
        'canonical_base_url',
        'ga_measurement_id',
        'search_console_verification',
        'logo_path',
    ];

    public static function defaults(): array
    {
        return [
            'site_name' => 'مطاعم مأرب',
            'site_name_en' => 'Marib Restaurant',
            'default_meta_description' => 'مطعم مأرب يقدم الأكلات الشعبية الأصيلة مع طلب أونلاين سريع وخيارات تخصيص متعددة.',
            'default_meta_description_en' => 'Marib Restaurant serves authentic Yemeni dishes with fast online ordering and flexible meal customization.',
            'home_meta_title' => 'مطاعم مأرب | أكلات شعبية أصيلة',
            'home_meta_title_en' => 'Marib Restaurant | Authentic Yemeni Food',
            'home_meta_description' => 'اكتشف منيو مطاعم مأرب واطلب وجبتك بسهولة مع خيارات تخصيص، توصيل سريع، وعروض موسمية.',
            'home_meta_description_en' => 'Discover the Marib Restaurant menu and order easily with custom options, fast delivery, and seasonal offers.',
            'support_phone' => '0138092388',
            'secondary_phone' => '0567510757',
            'whatsapp_number' => '966558111372',
            'primary_address' => 'الدمام - حي السلام',
            'primary_address_en' => 'Dammam - Al Salam District',
            'working_hours' => 'في خدمتكم 24 ساعة',
            'working_hours_en' => 'Open 24 hours',
            'google_maps_url' => 'https://maps.app.goo.gl/Fxpy7ndtqyVQovuZ7',
            'google_maps_embed_url' => 'https://www.google.com/maps?q=26.44908077907955,50.09990773653787&z=16&output=embed',
            'canonical_base_url' => config('app.url'),
        ];
    }
}
