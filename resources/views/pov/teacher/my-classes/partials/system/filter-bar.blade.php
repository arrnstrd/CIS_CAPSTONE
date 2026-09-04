<form method="GET" action="{{ route('teacher.grading-system') }}" class="gs-filter-bar mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="gs-filter-label">School Year</label>
            <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" @selected(!$selectedSchoolYearId)>All School Years</option>
                @foreach ($schoolYears as $sy)
                    <option value="{{ $sy->id }}" @selected($selectedSchoolYearId == $sy->id)>{{ $sy->school_year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Term</label>
            <select name="grading_period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach ($gradingPeriods as $gp)
                    <option value="{{ $gp->id }}" @selected($selectedGradingPeriodId == $gp->id)>Term {{ $gp->sequence }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>
