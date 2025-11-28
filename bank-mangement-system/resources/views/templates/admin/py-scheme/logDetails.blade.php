<div class="col-md-12 ">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12"></div>
                        <div class="col-lg-12">
                            <div id="timeline-container">
                                <div class="inner-container">
                               @foreach($log as $index => $record)
                                    <ul class="timeline">
                                        <li class="timeline-item" data-date="{{date('d- M-Y H:i:s', strtotime($record->created_at))}}">
                                            <div class="main-box">
                                                <h3>{{$record->	title}}</h3>
                                                <p>{{$record->description}}</p>
                                            </div>
                                        </li>
                                    </ul>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>