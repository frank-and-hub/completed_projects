
<div class="col-md-3 col-sm-6 content-card dashboard-card">
    <div class="card-big-shadow">
        <div class="card card-just-text" data-background="color" data-color="{{$color}}" data-radius="none">
            <div class="content">

                <i class="bx {{$icon}} icon-{{($color=='white')?'green':'white'}} mb-2"></i>
                <h6 class="category">{{$title}}</h6>
                <h4 class="title"><a href="{{$route }}" style="color:{{($color=='white')?'green':'white'}}"><span
                            id="{{$id}}" class="count">{{$count}}</span></a></h4>
            </div>
        </div>
    </div>
</div>


