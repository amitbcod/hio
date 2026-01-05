<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LanguageLoader
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load session if not already loaded
        if (!isset($this->CI->session)) {
            $this->CI->load->library('session');
        }

        $this->CI->load->helper(['language', 'cookie']);
    }

    public function initialize(): void
    {
          log_message('debug', 'LanguageLoader hook is called.');
        $shopcode = SHOPCODE ?? '';
        $shop_id = SHOP_ID ?? 0;

        $siteLang = $this->CI->session->userdata('site_lang');
        $multi_lang_flag = $this->CI->session->userdata('multi_lang_flag');

        // If multi-language is disabled, use session language
        if (!empty($multi_lang_flag) && $multi_lang_flag != 1) {
            $this->CI->lang->load('content', strtolower($siteLang ?? 'en'));
            return;
        }

        // Load shop details if multi_lang_flag is not set
        if (empty($multi_lang_flag)) {
            $FbcShopDetails = GlobalRepository::get_fbc_users_shop($shop_id)->result ?? null;

            $multi_flag = $FbcShopDetails->multi_lang_flag ?? 0;
            $this->CI->session->set_userdata(['multi_lang_flag' => $multi_flag]);

            if ($multi_flag != 1) {
                $this->CI->lang->load('content', strtolower($siteLang ?? 'en'));
                return;
            }
        }

        $all_languages = CommonRepository::get_all_multi_language_data() ?? [];

        // 1. Check GET parameter `locale`
        if (!empty($_GET['locale'])) {
            $query_lang = strtolower(mb_substr($_GET['locale'], 0, 2));
            $language = $this->checkQueryLang($query_lang, $all_languages);

            if ($language) {
                $this->setLanguage($language);
                return;
            }
        }

        // 2. Use session language if available
        if (!empty($siteLang)) {
            $this->CI->lang->load('content', strtolower($siteLang));
            return;
        }

        // 3. Check language cookie
        if (!empty($_COOKIE['site_language'])) {
            $language = $this->checkQueryLang($_COOKIE['site_language'], $all_languages);
            if ($language) {
                $this->setLanguage($language);
                return;
            }
        }

        // 4. Detect preferred browser language
        $preferred_language = get_preferred_browser_language() ?? '';
        if ($preferred_language !== '') {
            $language = $this->checkQueryLang($preferred_language, $all_languages);
            if ($language) {
                $this->setLanguage($language);
                return;
            }
        }

        // 5. Fallback to default language
        $this->setDefaultLanguage($shopcode, $shop_id);
    }

    private function checkQueryLang(string $query_lang, array $all_languages)
    {
        foreach ($all_languages as $language) {
            if (($language->code ?? '') === $query_lang) {
                return $language;
            }
        }
        return false;
    }

    private function setDefaultLanguage(string $shopcode, int $shop_id): void
    {
        $language_res = CommonRepository::get_default_language($shopcode, $shop_id) ?? null;

        if (!empty($language_res->languagedata) && ($language_res->is_success ?? '') === "true") {
            $this->setLanguage($language_res->languagedata);
        }
    }

    private function setLanguage($language): void
    {
        $lang_data = [
            'lid' => $language->id ?? 0,
            'site_lang' => strtolower($language->name ?? 'en'),
            'ldisplay_name' => $language->display_name ?? 'English',
            'lcode' => $language->code ?? 'en',
            'lis_default_language' => $language->is_default_language ?? 0,
        ];

        $this->CI->session->set_userdata($lang_data);
        $this->CI->lang->load('content', $lang_data['site_lang']);
    }
}
