<?php

namespace App\Http\Controllers;

use App\Models\ApplianceApplication;
use App\Models\LetterTemplate;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Example controller showing real-world usage
 */
class PermitController extends Controller
{
    public function generate(Request $request, $id)
    {
        $application = ApplianceApplication::with(['student', 'appliance'])->findOrFail($id);
        $template = LetterTemplate::where('type', 'permit')->first();

        $handler = new PlaceholderHandler;
        $content = $handler
            ->add('permitNo', 'MIMET/2024/C01/KTL003/'.$application->id)
            ->addDate('created_at', $application->created_at, 'd M Y')
            ->addDate('expires_at', now()->addYear(), 'd M Y')
            ->addNullable('studentName', $application->student->student_name)
            ->addNullable('matricNo', $application->student->matric_number)
            ->addNullable('applianceType', $application->appliance->type)
            ->addNullable('applianceName', $application->appliance->name)
            ->addNullable('serialNo', $application->serial_number)
            ->addNullable('approvedBy', $application->approvedBy->name)
            ->replace($template->content);

        return view('letters.preview', compact('content'));
    }

    public function generateWithContext(Request $request, $id)
    {
        $application = ApplianceApplication::with(['student', 'appliance'])->findOrFail($id);
        $template = LetterTemplate::where('type', 'permit')->first();

        // Using context registration for cleaner code
        $handler = new PlaceholderHandler;

        // Register contexts once
        $handler->registerContextMapping('student', [
            'name' => 'student_name',
            'matric' => 'matric_number',
            'program' => 'program.name',
        ]);

        $handler->registerContextMapping('appliance', [
            'type' => 'type',
            'name' => 'name',
            'serial' => 'serial_number',
        ]);

        $content = $handler
            ->add('permitNo', 'MIMET/2024/C01/KTL003/'.$application->id)
            ->addDate('created_at', $application->created_at, 'd M Y')
            ->addDate('expires_at', now()->addYear(), 'd M Y')
            ->useContext('student', $application->student, 'student')
            ->useContext('appliance', $application, 'appliance')
            ->addNullable('approvedBy', $application->approvedBy->name)
            ->replace($template->content);

        return view('letters.preview', compact('content'));
    }
}
