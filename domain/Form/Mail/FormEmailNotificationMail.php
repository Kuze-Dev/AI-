<?php

declare(strict_types=1);

namespace Domain\Form\Mail;

use App\Settings\FormSettings;
use Domain\Form\Models\FormEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
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
        protected readonly ?array $form_attachments = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(app(FormSettings::class)->sender_email, $this->formEmailNotification->sender_name),
            to: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->to ?? []),
            cc: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->cc ?? []),
            bcc: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->bcc ?? []),
            replyTo: array_map($this->interpolateStringWithData(...), $this->formEmailNotification->reply_to ?? [$this->formEmailNotification->sender_name]),
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

        $compiledTemplate = Blade::compileString($this->getNormalizedTemplate());

        $contents = Blade::render($compiledTemplate, $this->data);

        return new HtmlString((new CssToInlineStyles())->convert(
            $contents,
            View::make($this->getTheme(), $this->data)->render()
        ));
    }

    protected function buildTemplateText(): HtmlString
    {
        View::flushFinderCache();
        View::replaceNamespace('mail', $this->textComponentPaths());

        $compiledTemplate = Blade::compileString($this->getNormalizedTemplate());

        $contents = Blade::render($compiledTemplate, $this->data);

        return new HtmlString(html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", $contents) ?? '', ENT_QUOTES, 'UTF-8'));
    }

    protected function getNormalizedTemplate(): string
    {
        return <<<blade
            <x-mail::message>
            {$this->formEmailNotification->template}
            </x-mail::message>
            blade;
    }

    protected function getTheme(): string
    {
        $this->theme ??= config('mail.markdown.theme');

        if (View::exists($customTheme = Str::start($this->theme, 'mail.'))) {
            return $customTheme;
        }

        if (str_contains($this->theme, '::')) {
            return $this->theme;
        }

        return 'mail::themes.' . $this->theme;
    }

    protected function htmlComponentPaths(): array
    {
        return array_map(fn ($path) => $path . '/html', $this->componentPaths());
    }

    protected function textComponentPaths(): array
    {
        return array_map(fn ($path) => $path . '/text', $this->componentPaths());
    }

    protected function componentPaths(): array
    {
        return array_unique(array_merge(
            config('mail.markdown.paths'),
            [base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views')]
        ));
    }

    public function attachments(): array
    {
        $attach = [];

        if ($this->formEmailNotification->has_attachments && $this->form_attachments !== null) {

            foreach ($this->form_attachments as $value) {
                $attach[] = Attachment::fromStorageDisk('s3', $value)
                    ->as(basename($value));
            }
        }

        return $attach;

    }
}
