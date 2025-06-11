<?php

namespace App\Livewire;

use App\Services\CanvasService;
use Livewire\Component;

class CourseSelector extends Component
{
    public $searchTerm = '';
    public $searchResults = [];
    public $selectedCourses = [];
    public $allCourses = [];
    public $showResults = false;

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount()
    {
        $this->selectedCourses = [];
        $this->loadAllCourses();
    }

    public function updatedSearchTerm()
    {
        $this->filterCourses();
    }

    public function loadAllCourses()
    {
        try {
            $results = $this->canvasService->searchCourses('');
            $this->allCourses = $results->toArray();
            $this->filterCourses();
            $this->showResults = true;
        } catch (\Exception $e) {
            $this->allCourses = [];
            $this->searchResults = [];
            $this->showResults = false;
            session()->flash('error', 'Fout bij ophalen courses: ' . $e->getMessage());
        }
    }

    public function filterCourses()
    {
        if (strlen($this->searchTerm) >= 2) {
            $term = mb_strtolower($this->searchTerm);
            $this->searchResults = array_values(array_filter($this->allCourses, function ($course) use ($term) {
                return mb_stripos($course['name'], $term) !== false;
            }));
        } else {
            $this->searchResults = $this->allCourses;
        }
    }


    public function selectCourse($course)
    {
//        dd($course);
        // Prevent duplicates
        foreach ($this->selectedCourses as $selected) {
            if ($selected['id'] == $course['id']) {
                return;
            }
        }
        $this->selectedCourses[] = $course;
        session(['selected_courses' => $this->selectedCourses]);
    }

    public function removeCourse($courseId)
    {
        $this->selectedCourses = array_values(array_filter($this->selectedCourses, function ($course) use ($courseId) {
            return $course['id'] != $courseId;
        }));
    }

    public function proceedToModules()
    {
        session(['selected_courses' => $this->selectedCourses]);
        return redirect()->route('modules.select');
    }

    // ... selectCourse, removeCourse, clearSearch blijven gelijk ...

    public function render()
    {
        return view('livewire.course-selector');
    }
}
