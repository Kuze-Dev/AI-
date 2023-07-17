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

    $mailable = new FormEmailNotificationMail($formEmailNotification, []);
    $mailable->assertFrom($formEmailNotification->sender_name);
    $mailable->assertTo($formEmailNotification->to);
    $mailable->assertHasCc($formEmailNotification->cc);
    $mailable->assertHasBcc($formEmailNotification->bcc);
    $mailable->assertHasReplyTo($formEmailNotification->reply_to);
    $mailable->assertHasSubject($formEmailNotification->subject);
});
