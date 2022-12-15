<?php

declare(strict_types=1);

namespace Domain\Form\Mail;

use Domain\Form\Models\FormEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class FormEmailNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected array $componentPaths;

    public function __construct(
        protected readonly FormEmailNotification $formEmailNotification,
        protected readonly array $data,
    ) {
        $this->theme = config('mail.markdown.theme');
        $this->componentPaths = config('mail.markdown.paths');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->interpolateStringWithData($this->formEmailNotification->sender),
            to: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->to ?? []),
            cc: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->cc ?? []),
            bcc: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->bcc ?? []),
            replyTo: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->reply_to ?? [$this->formEmailNotification->sender]),
            subject: $this->interpolateStringWithData($this->formEmailNotification->subject),
        );
    }

    protected function interpolateStringWithData(string $string): string
    {
        if ($string !== $compiledString = Blade::compileEchos($string)) {
            return Blade::render($compiledString, $this->data);
        }

        return $string;
    }

    protected function buildView(): array
    {
        return [
            'html' => $this->buildTemplateHtml(),
            'text' => $this->buildTemplateText(),
        ];
    }

    protected function buildTemplateHtml(): HtmlString
    {
        View::flushFinderCache();
        View::replaceNamespace('mail', $this->htmlComponentPaths());

        $compiledTemplate = Blade::compileEchos($this->getNormalizedTemplate());

        $contents = Blade::render($compiledTemplate, $this->data);

        $theme = match (true) {
            View::exists($customTheme = Str::start($this->theme, 'mail.')) => $customTheme,
            str_contains($this->theme, '::') => $this->theme,
            default => 'mail::themes.' . $this->theme,
        };

        return new HtmlString((new CssToInlineStyles)->convert(
            $contents,
            View::make($theme, $this->data)->render()
        ));
    }

    protected function buildTemplateText(): HtmlString
    {
        View::flushFinderCache();
        View::replaceNamespace('mail', $this->textComponentPaths());

        $compiledTemplate = Blade::compileEchos($this->getNormalizedTemplate());

        $contents = Blade::render($compiledTemplate, $this->data);

        return new HtmlString(html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", $contents), ENT_QUOTES, 'UTF-8'));
    }

    protected function getNormalizedTemplate(): string
    {
        return <<<blade
            <x-mail::message>
            {$this->formEmailNotification->template}
            </x-mail::message>
            blade;
    }

    protected function htmlComponentPaths()
    {
        return array_map(fn ($path) => $path . '/html', $this->componentPaths());
    }

    protected function textComponentPaths()
    {
        return array_map(fn ($path) => $path . '/text', $this->componentPaths());
    }

    protected function componentPaths()
    {
        return array_unique(array_merge($this->componentPaths, [
            base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views')
        ]));
    }

    public function attachments(): array
    {
        return [];
    }
}
