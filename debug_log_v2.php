<?php
try {
    // 1. Find a valid attempt
    $attempt = \App\Models\ExamAttempt::where('status', '!=', 'completed')->latest()->first();
    
    if (!$attempt) {
        // Fallback to any attempt
        $attempt = \App\Models\ExamAttempt::latest()->first();
    }

    if (!$attempt) {
        echo "No attempt found at all.\n";
        exit;
    }

    echo "Found Attempt ID: " . $attempt->id . "\n";
    
    // 2. Login as the student user associated with this attempt
    // Identify user from student
    $student = \App\Models\Student::find($attempt->student_id);
    if (!$student) {
        echo "Student not found for attempt.\n";
        exit;
    }
    
    $user = \App\Models\User::find($student->user_id);
    if (!$user) {
        echo "User not found for student.\n";
        exit;
    }

    \Illuminate\Support\Facades\Auth::login($user);
    echo "Logged in as: " . $user->name . " (ID: $user->id)\n";

    // 3. Initialize Component and Log Violation
    $component = new \App\Livewire\Student\TakeExam();
    $component->examId = $attempt->exam_id;
    $component->attemptId = $attempt->id;
    
    // We need to set the attempt property manually or ensure it's loaded
    // Livewire components don't run mount() when instantiated like this
    // We can just call logViolation directly as long as properties are set
    
    // Use reflection to set protected property if needed, but attempt is accessed via method getAttemptProperty
    // which uses $this->attemptId. So setting $this->attemptId is enough.

    $component->logViolation('tab_switch', 'Debug Script Violation Check');

    echo "logViolation called.\n";
    
    // 4. Verify Creation
    $log = \App\Models\ExamActivity::where('exam_attempt_id', $attempt->id)
        ->where('message', 'Debug Script Violation Check')
        ->first();
        
    if ($log) {
        echo "SUCCESS: Log created with ID " . $log->id . "\n";
    } else {
        echo "FAILURE: Log not found in DB.\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
