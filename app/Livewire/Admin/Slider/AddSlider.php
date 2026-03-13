<?php

namespace App\Livewire\Admin\Slider;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Slide;

class AddSlider extends Component
{
    use WithFileUploads;
    public $photo;
    public $existedPhoto;
    public $title;
    public $description;
    public $link;
    public $sort_order = 0;
    public $status = 1;

    public function mount()
    {
        // Set default sort order to count + 1
        $countOrder = Slide::count();
        $this->sort_order = $countOrder + 1;
    }

    public function store()
    {
        $this->validate([
            'photo' => 'required|image|max:3072', // 1MB Max
            'title' => 'required',
        ], [
            'photo.image' => 'File không phải là ảnh.',
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'photo.max' => 'Ảnh không được lớn hơn 3MB.',
            'photo.required' => 'Vui lòng chọn ảnh.'
        ]);
        
        $imageName = time() . uniqid() . '.' . $this->photo->extension();
        $this->photo->storeAs(path: "public\images\slides", name: $imageName);

        $slider = new Slide();
        $slider->title = $this->title;
        $slider->description = $this->description;
        $slider->link = $this->link;
        $slider->image = $imageName;
        if($this->status == false){
            $this->status = 0;
        }else{
            $this->status = 1;
        }
        $slider->is_active = $this->status;
        $slider->save();

        // Always reorganize all sliders based on the input order
        // Get all sliders ordered by sort_order
        $sliders = Slide::orderBy('sort_order')->get();
        
        // Insert new slider at specified position
        $inserted = false;
        $updatedSliders = collect();
        $orderCounter = 1;
        
        foreach ($sliders as $sliderItem) {
            if (!$inserted && $orderCounter == $this->sort_order) {
                // Insert new slider here
                $slider->sort_order = $orderCounter;
                $updatedSliders->push($slider);
                $inserted = true;
                $orderCounter++;
            }
            
            // Update other slider's order
            $sliderItem->sort_order = $orderCounter;
            $updatedSliders->push($sliderItem);
            $orderCounter++;
        }
        
        // If not inserted yet (new order is last), insert at the end
        if (!$inserted) {
            $slider->sort_order = $orderCounter;
            $updatedSliders->push($slider);
        }
        
        // Save all updated sliders
        foreach ($updatedSliders as $updatedSlider) {
            $updatedSlider->save();
        }
        return redirect()->route('admin.sliders');
    }

    public function render()
    {
        return view('livewire.admin.slider.add-slider');
    }
}