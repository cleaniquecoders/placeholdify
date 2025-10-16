<?php

namespace App\Http\Controllers;

use App\Models\AcademicRecord;
use App\Models\EventInvitation;
use App\Models\LeaveRequest;
use App\Models\LetterTemplate;
use App\Models\OfferRequest;
use App\Models\RentalApplication;
use App\Services\Letters\AcademicWarningLetter;
use App\Services\Letters\EventInvitationLetter;
use App\Services\Letters\MedicalLeaveLetter;
use App\Services\Letters\OfferLetter;
use App\Services\Letters\RentalAgreementLetter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Comprehensive Letter Generation Controller
 *
 * Demonstrates practical usage of BaseLetter classes in a Laravel application
 */
class LetterGenerationController extends Controller
{
    /**
     * Generate employment offer letter
     */
    public function generateOfferLetter(Request $request, $offerId)
    {
        $offer = OfferRequest::with(['candidate', 'position.department'])->findOrFail($offerId);
        $template = LetterTemplate::where('type', 'offer_letter')->first();

        $letter = new OfferLetter;
        $content = $letter->generate($offer, $template->content);

        // Save to storage
        $filename = "offer_letters/offer_{$offer->id}_".now()->format('Y-m-d').'.txt';
        Storage::put($filename, $content);

        return response()->json([
            'success' => true,
            'content' => $content,
            'filename' => $filename,
            'download_url' => route('letters.download', ['file' => $filename]),
        ]);
    }

    /**
     * Generate academic warning letter with email notification
     */
    public function generateAcademicWarning(Request $request, $recordId)
    {
        $record = AcademicRecord::with([
            'student.program',
            'student.academicAdvisor',
            'failedSubjects',
        ])->findOrFail($recordId);

        $template = LetterTemplate::where('type', 'academic_warning')->first();

        $letter = new AcademicWarningLetter;
        $content = $letter->generate($record, $template->content);

        // Log the warning in database
        $record->warnings()->create([
            'warning_level' => $letter->getHandler()->all()['warning_level'],
            'content' => $content,
            'issued_date' => now(),
            'issued_by' => auth()->id(),
        ]);

        // Send email notification
        \Mail::to($record->student->email)->send(
            new \App\Mail\AcademicWarningMail($record->student, $content)
        );

        return view('academic.warning-letter', [
            'content' => $content,
            'student' => $record->student,
            'record' => $record,
        ]);
    }

    /**
     * Generate rental agreement with digital signatures
     */
    public function generateRentalAgreement(Request $request, $applicationId)
    {
        $application = RentalApplication::with([
            'tenant',
            'landlord',
            'property.venue',
            'furnishing',
            'houseRules',
        ])->findOrFail($applicationId);

        $template = LetterTemplate::where('type', 'rental_agreement')->first();

        $letter = new RentalAgreementLetter;

        // Use modifier support for flexible formatting
        $content = $letter->generateWithModifiers($application, $template->content);

        // Generate PDF
        $pdf = \PDF::loadView('rentals.agreement-pdf', [
            'content' => $content,
            'application' => $application,
        ]);

        $filename = "rental_agreements/agreement_{$application->id}.pdf";
        Storage::put($filename, $pdf->output());

        // Update application status
        $application->update([
            'status' => 'agreement_generated',
            'agreement_path' => $filename,
            'agreement_generated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'pdf_url' => Storage::url($filename),
            'agreement_reference' => $letter->getHandler()->all()['agreement_reference'],
        ]);
    }

    /**
     * Generate medical leave approval with workflow integration
     */
    public function generateMedicalLeave(Request $request, $leaveId)
    {
        $leave = LeaveRequest::with([
            'employee.department',
            'employee.manager',
            'approvedBy',
            'hrRepresentative',
        ])->findOrFail($leaveId);

        $template = LetterTemplate::where('type', 'medical_leave')->first();

        $letter = new MedicalLeaveLetter;
        $content = $letter->generate($leave, $template->content);

        // Update leave request status
        $leave->update([
            'status' => 'approved',
            'approval_letter' => $content,
            'approved_at' => now(),
        ]);

        // Trigger workflow events
        event(new \App\Events\MedicalLeaveApproved($leave));

        // Generate calendar entries for manager and HR
        $this->createLeaveCalendarEntries($leave);

        return response()->json([
            'success' => true,
            'content' => $content,
            'leave_reference' => $letter->getHandler()->all()['leave_reference'],
            'next_actions' => $this->getNextActions($leave),
        ]);
    }

    /**
     * Generate event invitation with RSVP tracking
     */
    public function generateEventInvitation(Request $request, $invitationId)
    {
        $invitation = EventInvitation::with([
            'guest.organization',
            'event.venue',
            'event.agenda',
            'contactPerson',
        ])->findOrFail($invitationId);

        $template = LetterTemplate::where('type', 'event_invitation')->first();

        $letter = new EventInvitationLetter;
        $content = $letter->generate($invitation, $template->content);

        // Create RSVP tracking record
        $invitation->update([
            'invitation_sent_at' => now(),
            'invitation_code' => $letter->getHandler()->all()['invitation_code'],
            'status' => 'sent',
        ]);

        // Send invitation email with RSVP link
        $rsvpUrl = route('events.rsvp', ['code' => $invitation->invitation_code]);

        \Mail::to($invitation->guest->email)->send(
            new \App\Mail\EventInvitationMail($invitation, $content, $rsvpUrl)
        );

        return response()->json([
            'success' => true,
            'content' => $content,
            'invitation_code' => $invitation->invitation_code,
            'rsvp_url' => $rsvpUrl,
        ]);
    }

    /**
     * Bulk letter generation
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:offer,warning,rental,leave,invitation',
            'ids' => 'required|array',
            'template_id' => 'required|exists:letter_templates,id',
        ]);

        $template = LetterTemplate::findOrFail($request->template_id);
        $results = [];

        foreach ($request->ids as $id) {
            try {
                $result = match ($request->type) {
                    'offer' => $this->generateOfferLetter($request, $id),
                    'warning' => $this->generateAcademicWarning($request, $id),
                    'rental' => $this->generateRentalAgreement($request, $id),
                    'leave' => $this->generateMedicalLeave($request, $id),
                    'invitation' => $this->generateEventInvitation($request, $id),
                };

                $results[] = [
                    'id' => $id,
                    'status' => 'success',
                    'data' => $result->getData(),
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $id,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'summary' => [
                'total' => count($request->ids),
                'successful' => count(array_filter($results, fn ($r) => $r['status'] === 'success')),
                'failed' => count(array_filter($results, fn ($r) => $r['status'] === 'error')),
            ],
        ]);
    }

    /**
     * Preview letter before generation
     */
    public function previewLetter(Request $request)
    {
        $request->validate([
            'type' => 'required|in:offer,warning,rental,leave,invitation',
            'id' => 'required',
            'template_content' => 'required|string',
        ]);

        $letter = match ($request->type) {
            'offer' => new OfferLetter,
            'warning' => new AcademicWarningLetter,
            'rental' => new RentalAgreementLetter,
            'leave' => new MedicalLeaveLetter,
            'invitation' => new EventInvitationLetter,
        };

        // Get the data based on type
        $data = $this->getDataForPreview($request->type, $request->id);

        // Generate preview without saving
        $content = $letter->generate($data, $request->template_content);
        $placeholders = $letter->getHandler()->all();

        return response()->json([
            'success' => true,
            'preview' => $content,
            'placeholders' => $placeholders,
            'available_placeholders' => array_keys($placeholders),
        ]);
    }

    private function createLeaveCalendarEntries($leave)
    {
        // Implementation for creating calendar entries
    }

    private function getNextActions($leave)
    {
        // Implementation for determining next workflow actions
        return [];
    }

    private function getDataForPreview($type, $id)
    {
        // Implementation for fetching data for preview
        return match ($type) {
            'offer' => OfferRequest::with(['candidate', 'position.department'])->findOrFail($id),
            'warning' => AcademicRecord::with(['student.program', 'failedSubjects'])->findOrFail($id),
            'rental' => RentalApplication::with(['tenant', 'landlord', 'property'])->findOrFail($id),
            'leave' => LeaveRequest::with(['employee.department'])->findOrFail($id),
            'invitation' => EventInvitation::with(['guest', 'event.venue'])->findOrFail($id),
        };
    }
}
