<div>
    <x-question-modal 
        wire:model="isOpen"
        :is-edit="$isEdit"
        :subjects="$subjects"
        :option-count="$optionCount"
        :editing-image-path="$editingImagePath"
        :question-image="$questionImage"
        :option-images="$optionImages"
        :editing-option-image-paths="$editingOptionImagePaths"
        :type="$questionForm['type']"
        :question-text="$questionForm['text']"
    />
    <x-latex-guide-modal />
</div>
