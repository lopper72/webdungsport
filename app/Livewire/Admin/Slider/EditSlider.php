<?php

namespace App\Livewire\Admin\Slider;

use Livewire\Component;
use App\Models\Slide;
use Livewire\WithFileUploads;

class EditSlider extends Component
{
    use WithFileUploads;
    public $id;
    public $photo;
    public $existedPhoto;
    public $title;
    public $description;
    public $link;
    public $sort_order = 0;
    public $status = 1;

    public function updateSlide()
    {
        $this->validate([
            'title' => 'required',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề.',
        ]);

        if ($this->photo) {
            $this->validate([
                'photo' => 'image|max:3072', // 1MB Max
            ], [
                'photo.image' => 'File không phải là ảnh.',
                'photo.max' => 'Ảnh không được lớn hơn 3MB.'
            ]);
            
            $imageName = time() . uniqid() . '.' . $this->photo->extension();
            $this->photo->storeAs(path: "public\images\slides", name: $imageName); 
        }

        $slider = Slide::find($this->id);
        $newSortOrder = $this->sort_order;
        
        // Always reorganize all sliders based on the input order
        // Get all sliders ordered by sort_order
        $sliders = Slide::orderBy('sort_order')->get();
        
        // Remove current slider from the list temporarily
        $currentSlider = $sliders->where('id', $this->id)->first();
        $sliders = $sliders->reject('id', $this->id);
        
        // Insert current slider at new position
        $inserted = false;
        $updatedSliders = collect();
        $orderCounter = 1;
        
        foreach ($sliders as $sliderItem) {
            if (!$inserted && $orderCounter == $newSortOrder) {
                // Insert current slider here
                $currentSlider->sort_order = $orderCounter;
                $updatedSliders->push($currentSlider);
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
            $currentSlider->sort_order = $orderCounter;
            $updatedSliders->push($currentSlider);
        }
        
        // Save all updated sliders
        foreach ($updatedSliders as $updatedSlider) {
            $updatedSlider->save();
        }
        
        // Update current slider with new data
        $slider->title = $this->title;
        $slider->description = $this->description;
        $slider->link = $this->link;
        if ($this->photo) {
            $slider->image = $imageName;
        }
        if($this->status == false){
            $this->status = 0;
        }else{
            $this->status = 1;
        }
        $slider->sort_order = $this->sort_order;
        $slider->is_active = $this->status;
        $slider->save();
        return redirect()->route('admin.sliders');
    }

    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $slide = Slide::find($this->id);
        $this->title = $slide->title;
        $this->description = $slide->description;
        $this->link = $slide->link;
        $this->sort_order = $slide->sort_order;
        if($slide->image){
            $this->existedPhoto = "images/slides/" . $slide->image;
        }
        $this->status = $slide->is_active;
        return view('livewire.admin.slider.edit-slider');
    }
}