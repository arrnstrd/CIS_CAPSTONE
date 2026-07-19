<x-layouts.teacher>
    <x-slot name="pageName">
        Student Management
    </x-slot>


<div class="card border mx-3 mb-3">
        <div class="card-body p-4">
            <form action="{{ route('teacher.student-management') }}" method="GET">

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-6">
                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                        <div class="input-group">
                            <input type="search" name="query" class="form-control" value="{{ request('query') }}" placeholder="Student number or name">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label text-muted text-uppercase small fw-bold">My Classes</label>
                        <select class="form-select" name="section_id" onchange="this.form.submit()">
                            <option value="" disabled >Choose section</option>
                            <option value="1">Grade 8 - Rizal</option>
                            <option value="2">Grade 8 - Bonifacio</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                        <a href="{{ route('teacher.student-management') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <x-ui.table>
        <thead>
            <tr>
                <th> LRN </th>
                <th>Name </th>
                <th>Grade & Section</th>
                <th>Actions</th>      
            </tr>
        </thead>
        <tbody>
            <tr>
                <td> </td>
            </tr>
        </tbody>
    </x-ui.table>




</x-layouts.teacher>