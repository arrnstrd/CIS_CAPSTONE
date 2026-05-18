<x-layouts.admin>

  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <x-ui.backButton> Back</x-ui.backButton>

  </div>


  <x-slot name="pageName">
    Grade Level
  </x-slot>

  <x-slot name="subtitle">
    Select a grade level to view the attendance
  </x-slot>



  {{-- cards --}}
  <div class="container-fluid ">
    <small class="fw-semibold text-muted ms-1 mb-3 text-uppercase "> primary education (elementary) </small>
      <div class="row g-3">
        <x-ui.grade>  <!--GRADE 1-->
          <x-slot name="grade">
            Grade 1
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>

        <x-ui.grade>  <!--GRADE 2-->
          <x-slot name="grade">
            Grade 2
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>

        <x-ui.grade>  <!--GRADE 3-->
          <x-slot name="grade">
            Grade 3
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>

        <x-ui.grade>   <!--GRADE 4-->
          <x-slot name="grade">
            Grade 4
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>

        <x-ui.grade>  <!--GRADE 5-->
          <x-slot name="grade">
            Grade 5
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>

        <x-ui.grade>   <!--GRADE 6-->
          <x-slot name="grade">
            Grade 6
          </x-slot>
          <x-slot name="gradelevel">
            primary level
          </x-slot>
        </x-ui.grade>
      </div>





  </div>



</x-layouts.admin>