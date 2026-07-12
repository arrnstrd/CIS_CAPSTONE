<x-layouts.admin>

    <div class="container-fluid py-4">
        <div class="row">
            
            <div class="col-12 col-md-5 col-lg-4 mb-4">
                <div class="d-flex flex-column align-items-center">
                    
                   <div class="mb-4 d-flex align-items-center justify-content-center text-center" 
                        style="width: 250px; height: 250px; border-radius: 50%; background-color: #d1d5db; overflow: hidden;">
                        <img src="{{ asset('images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo" class="img-fluid">
                    </div>

                    <div class="w-100 p-2 mb-3 fs-5 text-center fw-bold">
                        CONCEPCION INTEGRATED SCHOOL
                    </div>

                    <div class="w-100 p-3 text-center">
                        [Short Description Placeholder Text]
                    </div>

                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-8">
                
                <div class="border rounded-2 p-4 mb-5 bg-white">
                    <div class="mb-2">
                        <span class="fw-bold me-2" style="font-size: 0.85rem;">NAME</span> 
                        <span class="text-muted">[Sample Admin Name]</span>
                    </div>
                    <div>
                        <span class="fw-bold me-2" style="font-size: 0.85rem;">EMAIL</span> 
                        <span class="text-muted">[sample.admin@example.com]</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-end mb-2">
                    <div>
                        <h6 class="mb-1 text-uppercase fw-bold" style="font-size: 0.9rem;">School Year</h6>
                        <span class="text-muted" style="font-size: 0.85rem;">short description for adding and managing school year</span>
                    </div>
                    <button class="btn btn-sm btn-dark rounded px-3" >
                        + Add New
                    </button>
                </div>

                <div class="table-responsive">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th> School year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                            </tr>
                        </tbody>
                    </x-ui.table>
                </div>

            </div>
        </div>
    </div>
</x-layouts.admin>