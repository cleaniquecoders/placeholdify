<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\BaseLetter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Event Invitation Letter Generator
 */
class EventInvitationLetter extends BaseLetter
{
    protected function configure(): void
    {
        $this->handler->setFallback('TBA');

        // Register guest context
        $this->handler->registerContext('guest', [
            'name' => 'full_name',
            'title' => 'professional_title',
            'organization' => 'organization.name',
            'email' => 'email',
        ]);

        // Register event context
        $this->handler->registerContext('event', [
            'name' => 'event_name',
            'type' => 'event_type',
            'venue' => 'venue.name',
            'address' => 'venue.full_address',
            'organizer' => 'organizer.name',
        ]);

        // Register time formatter
        $this->handler->registerFormatter('time', function ($value, $format = 'g:i A') {
            if ($value instanceof \DateTime) {
                return $value->format($format);
            }

            return \DateTime::createFromFormat('H:i:s', $value)->format($format);
        });
    }

    public function build($invitationData): PlaceholderHandler
    {
        return $this->handler
            ->useContext('guest', $invitationData->guest, 'guest')
            ->useContext('event', $invitationData->event, 'event')
            ->addDate('event_date', $invitationData->event->event_date, 'l, F j, Y')
            ->addFormatted('start_time', $invitationData->event->start_time, 'time')
            ->addFormatted('end_time', $invitationData->event->end_time, 'time')
            ->addDate('rsvp_deadline', $invitationData->rsvp_deadline, 'F j, Y')
            ->addDate('invitation_date', now(), 'F j, Y')
            ->add('invitation_code', $this->generateInvitationCode($invitationData))
            ->add('dress_code', $invitationData->event->dress_code ?? 'Business Casual')
            ->add('special_instructions', $this->formatSpecialInstructions($invitationData))
            ->add('agenda_highlights', $this->formatAgenda($invitationData->event->agenda))
            ->add('parking_info', $invitationData->event->venue->parking_info ?? 'Parking available on-site')
            ->add('contact_person', $invitationData->contact_person->name)
            ->add('contact_phone', $invitationData->contact_person->phone)
            ->add('contact_email', $invitationData->contact_person->email)
            ->addIf($invitationData->is_vip_guest, 'vip_treatment',
                'As our VIP guest, you will receive special seating and exclusive networking opportunities.',
                '')
            ->addIf($invitationData->requires_rsvp, 'rsvp_text',
                'Please confirm your attendance by responding to this invitation.',
                'No RSVP required.')
            ->addLazy('dietary_accommodations', function () use ($invitationData) {
                return $this->getDietaryAccommodations($invitationData);
            });
    }

    private function generateInvitationCode($invitationData): string
    {
        $eventCode = strtoupper(substr($invitationData->event->event_name, 0, 3));
        $guestCode = substr(md5($invitationData->guest->email), 0, 6);

        return $eventCode.'-'.now()->year.'-'.strtoupper($guestCode);
    }

    private function formatSpecialInstructions($invitationData): string
    {
        $instructions = [];

        if ($invitationData->requires_id) {
            $instructions[] = 'Please bring a valid photo ID for entry';
        }

        if ($invitationData->security_clearance_required) {
            $instructions[] = 'Security clearance verification required at entrance';
        }

        if ($invitationData->event->has_photography) {
            $instructions[] = 'Photography and videography will take place during the event';
        }

        if ($invitationData->gift_policy) {
            $instructions[] = $invitationData->gift_policy;
        }

        if (empty($instructions)) {
            return 'No special instructions';
        }

        return collect($instructions)->map(function ($instruction, $index) {
            return "• {$instruction}";
        })->join("\n");
    }

    private function formatAgenda($agenda): string
    {
        if (empty($agenda)) {
            return 'Detailed agenda will be provided upon arrival';
        }

        return collect($agenda)->map(function ($item) {
            $time = \DateTime::createFromFormat('H:i:s', $item['time'])->format('g:i A');

            return "{$time} - {$item['activity']}";
        })->join("\n");
    }

    private function getDietaryAccommodations($invitationData): string
    {
        if (! $invitationData->event->provides_meals) {
            return 'No meals will be provided at this event';
        }

        if ($invitationData->guest->dietary_restrictions) {
            return "We have noted your dietary restrictions: {$invitationData->guest->dietary_restrictions}. ".
                   'Special arrangements will be made to accommodate your needs.';
        }

        return 'Standard catering will be provided. Please inform us of any dietary restrictions.';
    }
}
