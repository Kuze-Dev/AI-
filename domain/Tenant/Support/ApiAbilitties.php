<?php

declare(strict_types=1);

namespace Domain\Tenant\Support;

enum ApiAbilitties: string
{
    case all = '*';
    case taxonomy_view = 'taxonomy:view';
    case taxonomyterms_view = 'taxonomyterms:view';
    case content_view = 'content:view';
    case contententry_view = 'contententry:view';
    case page_view = 'page:view';
    case global_view = 'global:view';
    case menu_view = 'menu:view';
    case form_view = 'form:view';
    case form_submit = 'form:submit';
    case generate_bucket_url = 'generate:bucket_url';
    case cms_search = 'cms:search';
    case settings_form = 'settings:form';
    case settings_site = 'settings:site';
    case media_view = 'media:view';

    /**
     * @return array<string>
     */
    public static function cmsWebSiteAbilities(): array
    {
        return [
            self::taxonomy_view->value,
            self::taxonomyterms_view->value,
            self::content_view->value,
            self::contententry_view->value,
            self::page_view->value,
            self::global_view->value,
            self::menu_view->value,
            self::form_view->value,
            self::form_submit->value,
            self::generate_bucket_url->value,
            self::cms_search->value,
            self::settings_form->value,
            self::settings_site->value,
            self::media_view->value,
        ];
    }

    public static function cmsCustomerAbilities(): array
    {
        return [
            self::taxonomy_view->value,
            self::taxonomyterms_view->value,
            self::content_view->value,
            self::contententry_view->value,
            self::page_view->value,
            self::global_view->value,
            self::menu_view->value,
            self::form_view->value,
            self::form_submit->value,
            self::generate_bucket_url->value,
            self::cms_search->value,
            self::media_view->value,
        ];
    }
}
