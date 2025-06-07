<?php

namespace App\Livewire;

use App\Services\CanvasService;
use Livewire\Component;

class ModuleSelector extends Component
{
    public $selectedCourses = [];
    public $availableModules = [];
    public $selectedModules = [];

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount($selectedCourses = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->loadModules();
    }

    public function loadModules()
    {
        $modules = [];
        foreach ($this->selectedCourses as $course) {
            $courseModules = $this->canvasService->getModules($course['id']);
            foreach ($courseModules as $module) {
                $modules[] = array_merge($module, ['course_id' => $course['id'], 'course_name' => $course['name']]);
            }
        }
        $this->availableModules = $modules;
    }

    public function selectModule($moduleIndex)
    {
        $module = $this->availableModules[$moduleIndex];
        $this->selectedModules[] = $module;
        unset($this->availableModules[$moduleIndex]);
        $this->availableModules = array_values($this->availableModules);
    }

    public function deselectModule($moduleIndex)
    {
        $module = $this->selectedModules[$moduleIndex];
        $this->availableModules[] = $module;
        unset($this->selectedModules[$moduleIndex]);
        $this->selectedModules = array_values($this->selectedModules);
    }

    public function proceedToStudents()
    {
        session([
            'selected_courses' => $this->selectedCourses,
            'selected_modules' => $this->selectedModules,
        ]);
        return redirect()->route('students.select');
    }

    public function render()
    {
        return view('livewire.module-selector');
    }
}
