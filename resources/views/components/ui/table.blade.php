    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <div class="container-fluid ">
        <div class="table-panel ">
            <table class="table table-hover  shadow-sm  align-middle">
              <thead>
                <tr>
                  {{$thead }}
                </tr>
              </thead>

                <tbody>
                    
                        {{$tbody  }}
                    
                </tbody>
            </table>
        </div>
    </div>
