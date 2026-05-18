<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\SubmitFormAnswersRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use App\Services\UserFormSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserFormController extends ApiController
{
    public function __construct(private readonly UserFormSubmissionService $formService) {}

    /**
     * User: start a form session (or resume existing one).
     * Returns the form with questions + the session end time.
     */
    public function start(Request $request, string $formUuid): JsonResponse
    {
        $form = Form::where('uuid', $formUuid)->where('active', true)
            ->with('questions.answers')
            ->withCount('questions')
            ->firstOrFail();

        $userForm = $this->formService->startForm($request->user(), $form);

        return $this->success(__('messages.retrieved'), [
            'form'     => new FormResource($form),
            'session'  => [
                'start_at' => $userForm->start_at,
                'end_at'   => $userForm->end_at,
                'submitted' => $this->formService->hasSubmitted($request->user()->id, $form->id),
            ],
        ]);
    }

    /**
     * User: submit form answers.
     * Auto-grades MCQ, stores text answers as correct.
     */
    public function submit(SubmitFormAnswersRequest $request, string $formUuid): JsonResponse
    {
        $form = Form::where('uuid', $formUuid)->where('active', true)->firstOrFail();

        if ($this->formService->hasSubmitted($request->user()->id, $form->id)) {
            return $this->error(__('messages.form_already_submitted'), 409);
        }

        $validated = $request->validated();

        $userForm = $this->formService->submitForm(
            $request->user(),
            $form,
            $validated['questions'],
            (int) ($validated['minutes_remaining'] ?? 0),
        );

        return $this->success(__('messages.created'), [
            'mark'     => $userForm->mark,
            'duration' => $userForm->duration,
        ]);
    }
}
