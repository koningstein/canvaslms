<?php

namespace App\Livewire;

use App\Services\CanvasService;
use Livewire\Component;

class StudentSelector extends Component
{
    public $selectedCourses = [];
    public $selectedModules = [];
    public $availableUsers = [];
    public $selectedUsers = [];

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount($selectedCourses = [], $selectedModules = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->selectedModules = $selectedModules;
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $users = [];
        foreach ($this->selectedCourses as $course) {
            $courseUsers = $this->canvasService->getUsers($course['id']);
            foreach ($courseUsers as $user) {
                $users[] = array_merge($user, ['course_id' => $course['id'], 'course_name' => $course['name']]);
            }
        }

        // Verwijder duplicaten op basis van e-mail of id
        $this->availableUsers = collect($users)->unique('email')->values()->toArray();
    }

    public function selectUser($userIndex)
    {
        $user = $this->availableUsers[$userIndex];
        $this->selectedUsers[] = $user;
        unset($this->availableUsers[$userIndex]);
        $this->availableUsers = array_values($this->availableUsers);
    }

    public function deselectUser($userIndex)
    {
        $user = $this->selectedUsers[$userIndex];
        $this->availableUsers[] = $user;
        unset($this->selectedUsers[$userIndex]);
        $this->selectedUsers = array_values($this->selectedUsers);
    }

    public function selectAllUsers()
    {
        $this->selectedUsers = array_merge($this->selectedUsers, $this->availableUsers);
        $this->availableUsers = [];
    }

    public function deselectAllUsers()
    {
        $this->availableUsers = array_merge($this->availableUsers, $this->selectedUsers);
        $this->selectedUsers = [];
    }

    public function showResult()
    {
        session([
            'selected_courses' => $this->selectedCourses,
            'selected_modules' => $this->selectedModules,
            'selected_users' => $this->selectedUsers,
        ]);

        return redirect()->route('results.progress');
    }

    public function render()
    {
        return view('livewire.student-selector');
    }
}
