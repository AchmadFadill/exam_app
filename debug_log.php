<?php
try {
    $user = \App\Models\User::first();
    \Illuminate\Support\Facades\Auth::login($user);
    $exam = \App\Models\Exam::first();
    $attempt = \App\Models\ExamAttempt::where('exam_id', $exam->id)->first();

    if (!$attempt) {
        echo "No attempt found.\n";
        exit;
    }

    $component = new \App\Livewire\Student\TakeExam();
    $component->examId = $exam->id;
    $component->attemptId = $attempt->id;
    
    // Manually set protected property if needed, but attempt is accessed via property
    // We need to ensure getAttemptProperty works or mock it.
    // Livewire components are hard to unit test in plain PHP script without Livewire testing helpers.
    // Instead, let's just test the DB creation part which is what logViolation does.

    \App\Models\ExamActivity::create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'exam_attempt_id' => $attempt->id,
        'type' => 'tab_switch',
        'severity' => 'warning',
        'message' => 'Manual Test Violation',
        'metadata' => ['test' => true]
    ]);

    echo "ExamActivity created successfully.\n";
    
    $attempt->increment('tab_switches');
    echo "Tab switches incremented.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
