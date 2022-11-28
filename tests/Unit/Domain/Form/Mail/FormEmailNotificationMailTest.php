<?php

declare(strict_types=1);

use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\FormEmailNotification;

beforeEach(fn () => testInTenantContext());

it('generate mail', function () {
    /** @var FormEmailNotification $formEmailNotification */
    $formEmailNotification = FormEmailNotificationFactory::new()
        ->for(
            FormFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->createOne();

    $mailable = new FormEmailNotificationMail($formEmailNotification);
    $mailable->assertFrom($formEmailNotification->sender);
    $mailable->assertTo(explode(',', $formEmailNotification->recipient));
    $mailable->assertHasCc(explode(',', $formEmailNotification->cc));
    $mailable->assertHasBcc(explode(',', $formEmailNotification->bcc));
    $mailable->assertHasReplyTo($formEmailNotification->reply_to);
    $mailable->assertHasSubject('Form Submission for '.$formEmailNotification->form->name);
});
