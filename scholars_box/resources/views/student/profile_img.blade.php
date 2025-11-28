<div class="profile-info-widget">
    <style>
        #profileImage {
            cursor: pointer;
            text-align: center;
        }
    </style>
    <form class="booking-pro-img" id="imageForm" name="imageForm" method="method" action="#"
        enctype="multipart/form-data">
        @csrf
        <input type="file" name="avatar" id="image" class="image_logo d-none"
            accept="image/png, image/jpeg, image/jpg, image/webp" />
        <a href="#" data-bs-toggle="modal" id="previewProfile" data-bs-target="#profileModal">
            <img src="{{ $user->avatar ? asset($user->avatar) : asset('images/images.jpeg') }}" alt="User Image">
        </a>
    </form>
    <script>
        document.getElementById('previewProfile').addEventListener('click', function(e) {
            e.preventDefault();
            // document.getElementById('image').click();
        });

        document.getElementById('image').addEventListener('change', function() {
            previewProfileImage(this);
        });

        function dataURItoBlob(dataURI) {
            var byteString = atob(dataURI.split(',')[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], {
                type: 'image/jpeg'
            });
        }

        function sendImageToServer(formData) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', "{{ route('Student.avatar') }}", true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log(response);
                    new Noty({
                        text: 'Profile Picture updated successfully!',
                        timeout: 3000
                    }).show();
                    var closeButton = document.querySelector('.close');
                    if (closeButton) {
                        closeButton.click();
                    }
                } else {
                    console.log('Profile not updated. Status:', xhr.status);
                }
            };
            xhr.send(formData);
        }

        function previewProfileImage(uploader) {
            if (uploader.files && uploader.files[0]) {
                var imageFile = uploader.files[0];

                if (imageFile) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewProfile').getElementsByTagName('img')[0].src = e.target.result;
                    };
                    reader.readAsDataURL(imageFile);
                }

                var formData = new FormData(document.forms['imageForm']);

                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                sendImageToServer(formData);
            }
        }
    </script>
    @php
        $user = Auth::user(); // Assuming you are using Laravel's authentication system

        $completionPercentage = $user->profileCompletionPercentage();
    @endphp
    <div class="profile-det-info">
        <h3>{{ ucwords($user->first_name . ' ' . $user->last_name) }}</h3>
        {{-- <div class="student-details">
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completionPercentage }}%;"
                    aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0"
                    aria-valuemax="{{ $completionPercentage }}">{{ $completionPercentage }} %</div>
            </div>
            <span>Profile Completed</span>
        </div> --}}
    </div>
</div>
