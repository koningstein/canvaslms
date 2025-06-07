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
    public $sections = [];
    public $selectedSections = []; // Now an array
    public $lastSelectedSectionId = null;

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount($selectedCourses = [], $selectedModules = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->selectedModules = $selectedModules;
        $this->loadUsersAndSections();
    }

    public function loadUsersAndSections()
    {
        $users = [];
        $sections = [];
        foreach ($this->selectedCourses as $course) {
            $courseUsers = $this->canvasService->getUsers($course['id']);
            foreach ($courseUsers as $user) {
                $users[$user['id']] = array_merge($user, ['course_id' => $course['id'], 'course_name' => $course['name']]);
            }
            $courseSections = $this->canvasService->getSections($course['id']);
            foreach ($courseSections as $section) {
                $sections[] = [
                    'id' => $section['id'],
                    'name' => $section['name'],
                    'course_id' => $course['id'],
                ];
            }
        }
        $this->availableUsers = array_values($users);
        $this->sections = $sections;
    }

    public function selectAllUsersInSections()
    {
        if (empty($this->selectedSections)) return;
        $usersInSections = [];
        foreach ($this->selectedCourses as $course) {
            foreach ($this->selectedSections as $sectionId) {
                $sectionUsers = $this->canvasService->getUsersInSection($course['id'], $sectionId);
                foreach ($sectionUsers as $user) {
                    $usersInSections[$user['id']] = array_merge($user, ['course_id' => $course['id'], 'course_name' => $course['name']]);
                }
            }
        }
        foreach ($usersInSections as $user) {
            if (!collect($this->selectedUsers)->contains('id', $user['id'])) {
                $this->selectedUsers[] = $user;
                $this->availableUsers = array_values(array_filter($this->availableUsers, fn($u) => $u['id'] != $user['id']));
            }
        }
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

    // Update method
    public function selectAllUsersInSectionsButton($sectionId)
    {
        $this->lastSelectedSectionId = $sectionId;
        $usersInSection = [];
        foreach ($this->selectedCourses as $course) {
            $sectionUsers = $this->canvasService->getUsersInSection($course['id'], $sectionId);
            foreach ($sectionUsers as $user) {
                $usersInSection[$user['id']] = array_merge($user, [
                    'course_id' => $course['id'],
                    'course_name' => $course['name']
                ]);
            }
        }

        // Only move users that are in availableUsers
        $availableUsersById = collect($this->availableUsers)->keyBy('id');
        foreach ($usersInSection as $userId => $user) {
            if ($availableUsersById->has($userId)) {
                $this->selectedUsers[] = $availableUsersById[$userId];
                $this->availableUsers = array_values(array_filter($this->availableUsers, fn($u) => $u['id'] != $userId));
            }
        }
    }

    public function render()
    {
        return view('livewire.student-selector');
    }
}
