 @foreach ($parkimages as $parkimage)
     <div @class([
         'jquery-uploader-card',
         'mb-4 mt-3',
         'box-shadow' => $parkimage->set_as_banner == 1,
     ]) id="{{ $parkimage->img_tmp_id }}" image-id="{{ $parkimage->id }}">
         <div class="jquery-uploader-preview-main">
             <div class="d-row d-flex justify-content-start" style="z-index:999; position: absolute;">
                 <div>
                     @can('users-show')
                         @if ($parkimage->user)
                             <a href="{{ $parkimage->user->hasRole('subadmin') ? route('admin.subadmin.details', $parkimage->user->id) : route('admin.user.view', $parkimage->user->id) }}"
                                 class="badge badge-primary"><i @class([
                                     'bx',
                                     'bxs-user' => !$parkimage->user->hasRole('subadmin'),
                                     'bxs-user-detail' => $parkimage->user->hasRole('subadmin'),
                                 ])></i class="text-white">
                                 {{ $parkimage->user->name }}</a>
                         @endif
                     @endcan

                 </div>
             </div>
             <div class="jquery-uploader-preview-action" onclick="ViewImage(this)">
             </div>
             <div class="img-btns-group d-none">
                 <div class="img-btns">
                     <i class="fa fa-trash-o text-white deleteFile" aria-hidden="true"
                         park_image_id="{{ $parkimage->id }}" park_id ="{{$parkimage->park_id}}" user_id="{{(!empty($parkimage->user_id))?$parkimage->user_id:null}}" ></i>
                     <button
                         @class([
                             'btn',
                             'btn-sm',
                             'btn-primary' => $parkimage->set_as_banner != 1,
                             'bannerBtn' => $parkimage->set_as_banner != 1,
                             'btn-danger' => $parkimage->set_as_banner == 1,
                             'removeBannerBtn' => $parkimage->set_as_banner == 1,
                         ])>{{ $parkimage->set_as_banner == 1 ? 'Unset Banner' : 'Set As Banner' }}</button>
                 </div>
             </div>
             <div class="jquery-uploader-preview-progress" style="display: none;">
                 <div class="progress-mask" style="height: 0%;"></div>
                 <div class="progress-loading">

                     <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                 </div>
             </div>

             <img src="{{ Storage::url($parkimage->media->path) }}" alt="preview" class="files_img"
                 onclick="ViewImage(this)">

             <a alt="preview" class="gallery-img" src="{{ Storage::url($parkimage->media->path) }}"
                 href="{{ $parkimage->media->full_path }}"
                 data-lcl-author="{{ $parkimage->user ? $parkimage->user->name : 'Parkscape' }}"
                 data-lcl-thumb="{{ $parkimage->media->full_path }}">
             </a>

         </div>
         <div class="form-check mt-1 "
             style="position: relative;
                        text-align: center;
                        top: 6px;">
             <input class="form-check-input" type="checkbox" value="{{ $parkimage->id }}"
                 style="outline: 1px solid var(--primary);" id="defaultCheck1" name="parkimage">

         </div>
         @if ($parkimage->set_as_banner == 1)
             <div class="check-mark"> <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                     fill="#2fa224" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                     <path
                         d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z">
                     </path>
                 </svg> </div>
         @endif
     </div>
 @endforeach



 @push('script')
     <script>
         var oldIndexVal = [];
         @if (!empty($parkimages))
             @foreach ($parkimages as $parkimage)
                 @if (!empty($parkimage->sort_index))
                     oldIndexVal.push({{ $parkimage->sort_index }});
                 @endif
             @endforeach
         @endif
     </script>
 @endpush
