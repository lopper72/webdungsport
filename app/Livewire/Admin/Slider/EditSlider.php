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
        
        // re-oder
        $this->reorderSlides();
      
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

    private function reorderSlides()
    {
        // Get all slides ordered by sort_order
        $slides = Slide::orderBy('sort_order')->get();
        
        // Reorder starting from 1
        $newOrder = 1;
        foreach ($slides as $slide) {
            $slide->sort_order = $newOrder;
            $slide->save();
            $newOrder++;
        }
    }
}