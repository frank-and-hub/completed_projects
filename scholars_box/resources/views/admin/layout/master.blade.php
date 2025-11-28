<!DOCTYPE html>
<!--
Template Name: NobleUI - Laravel Admin Dashboard Template
Author: NobleUI
Website: https://www.nobleui.com
Portfolio: https://themeforest.net/user/nobleui/portfolio
Contact: nobleui123@gmail.com
Purchase: https://1.envato.market/nobleui_laravel
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html>
<head>
  <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="Responsive Laravel Admin Dashboard Template based on Bootstrap 5">
	<meta name="author" content="NobleUI">
	<meta name="keywords" content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, laravel, theme, front-end, ui kit, web">

  <title>ScholarsBox - ScholarsBox Admin</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('admin/assets/plugins/dropify/css/dropify.min.css') }}">
  <!-- End fonts -->
  
  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">
  
  <link rel="shortcut icon" href="{{ asset('admin/favicon.ico') }}">

  <!-- plugin css -->
  <link href="{{ asset('admin/assets/fonts/feather-font/css/iconfont.css') }}" rel="stylesheet" />
  <link href="{{ asset('admin/assets/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />
 
  <!-- end plugin css -->

  @stack('plugin-styles')

  <!-- common css -->
  <link href="{{ asset('admin/css/app.css') }}" rel="stylesheet" />
  <!-- end common css -->

  @stack('style')
  <style>
  #myChart {
  height: 100%;
  width: 100%;
  min-height: 150px;
}
 
.zc-ref {
  display: none;
}
label{
  margin-left: 10px;
  margin-bottom: 5px;
}
</style>
  <script src="{{asset('admin/js/zingchart.min.js')}}"></script>
  
</head>
<body data-base-url="{{url('/')}}">


  <script src="{{ asset('admin/assets/js/spinner.js') }}"></script>

  <div class="main-wrapper" id="app">
    @include('admin.layout.sidebar')
    <div class="page-wrapper">
      
      @include('admin.layout.header')
      <div class="page-content">
        @yield('content')
      </div>
      @include('admin.layout.footer')
    </div>
  </div>

    <!-- base js -->
    <script src="{{ asset('admin/js/app.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="{{ asset('admin/assets/plugins/dropify/js/dropify.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/dropify.js') }}"></script>

    <!-- end base js -->
    <script>
    // Check if a success message exists in the session
    @if (session('success'))
        // Display the success message using Toastr
        toastr.success("{{ session('success') }}", 'Success', { timeOut: 5000 });
    @endif
</script>
    <!-- plugin js -->
    @stack('plugin-scripts')
    <!-- end plugin js -->

    <!-- common js -->
    <script src="{{ asset('admin/assets/js/template.js') }}"></script>
    <!-- end common js -->

    @stack('custom-scripts')
    <script>
      
      zingchart.loadModules('maps,maps-ind');
      
       
       var arrayOfColors = ['#EF9A9A #E57373', '#F48FB1 #F06292', '#B39DDB #9575CD', '#90CAF9 #64B5F6', '#80DEEA #4DD0E1', '#80CBC4 #4DB6AC', '#A5D6A7 #81C784', '#E6EE9C #DCE775', '#FFE082 #FFD54F', '#FFAB91 #FF8A65'];
       var colorIndex = 0;
       var listOfStates = ["gj","ka"];
        
       /* 
        *  need this function to render the first map load with random colors
        */
       var objectStates = function(arrayOfStates) {
         var objectOfStates = {};
         for (var i = 0; i < arrayOfStates.length; i++) {
           var itemId = arrayOfStates[i].toUpperCase();
           colorIndexCheck();
           objectOfStates[itemId] = {
             'background-color': arrayOfColors[colorIndex++]
           }
         }
        
         return objectOfStates;
       }(listOfStates);
        
       console.log('---- object form of states ----', objectStates);
        
       // initial config for first chart
       var myConfig = {
         shapes: [{
           type: 'zingchart.maps',
           options: {
             name: 'ind',
        
             zooming: false,
             panning: false,
             scrolling: false,
        
             style: {
               controls: {
                 visible: false
               },
               fillType: 'radial',
               cursor: 'pointer',
               hoverState: {
                 alpha: 0.3,
                 backgroundColor: 'white',
               },
               items: objectStates, //include specific shape regions with unique styles
               tooltip: {
                 alpha: 0.8,
                 backgroundColor: 'white',
                 borderColor: 'white',
                 borderRadius: 3,
                 fontColor: 'black',
                 fontFamily: 'Georgia',
                 fontSize: 12,
                 textAlpha: 1
               }
             }
           }
         }]
       };
        
       zingchart.render({
         id: 'myChart',
         data: myConfig,
         height: '100%',
         width: '100%'
       });
        
        
       // use this function to assign random colors 
       zingchart.bind('myChart', 'dataparse', function(e, oGraph) {
         console.log(arguments)
        
         // if graphset exists
         if (oGraph && oGraph.graphset[0]) {
           // if shapes exist
           if (oGraph.graphset[0].shapes) {
             oGraph.graphset[0].shapes = oGraph.graphset[0].shapes.map(function(curVal) {
               if (curVal && curVal['map-item']) {
                 colorIndexCheck();
                 curVal['background-color'] = arrayOfColors[colorIndex++];
               }
        
               return curVal;
             });
           }
         }
        
         return oGraph;
       });
        
       //drilldown chart configuration
       var drilldownConfig = {
         shapes: [{ //Drilldown maps.
             type: 'zingchart.maps',
             options: {
               name: '',
        
               zooming: false,
               panning: false,
               scrolling: false,
        
               style: {
                 controls: {
                   visible: false
                 },
                 backgroundColor: 'pink',
                 hoverState: {
                   alpha: 0.3,
                   backgroundColor: 'white',
                 },
                 tooltip: {
                   alpha: 0.8,
                   backgroundColor: 'white',
                   borderColor: 'white',
                   borderRadius: 3,
                   fontColor: 'black',
                   fontFamily: 'Georgia',
                   fontSize: 12,
                   textAlpha: 1
                 }
               }
             }
           },
           { //Button to go back to main map.
             id: 'back_button',
             type: 'rectangle',
             height: 25,
             width: 40,
             x: 20,
             y: 20,
        
             backgroundColor: '#C4C4C4',
             borderRadius: 3,
             cursor: 'hand',
             hoverState: {
               alpha: 0.3,
               backgroundColor: 'white'
             },
             label: {
               text: 'Back',
             }
           }
         ]
       };
        
       // stringify the maps to load them into page using loadModules()
       var stringifyMapList = listOfStates.reduce(function(acc, curVal, index) {
         if (index === 1)
           acc = 'maps-ind_' + acc;
         return acc + ',' + 'maps-ind_' + curVal;
       });
       console.log('---stringified map modules ----', stringifyMapList);
        
       // for maps that exist in our library. Pre-load them later on
       zingchart.loadModules(stringifyMapList);
        
       /*
        * shape click is when we render a new chart or load the original chart
        */
       zingchart.bind('myChart', 'shape_click', function(e) {
         var newMapId = 'ind_' + String(e.shapeid).toLowerCase();
         var shapeId = e.shapeid;
        
         // if shape is our back button and not the map
         if (shapeId == 'back_button') {
           // since we are using setdata, reload will reload the original chartJSON
           zingchart.exec('myChart', 'reload');
           return;
         }
        
         if (hasDrilldownData(newMapId)) {
           drilldownConfig.shapes[0].options.name = newMapId;
           zingchart.exec('myChart', 'setdata', {
             data: drilldownConfig
           });
         }
       });
        
       // used in the shape_click event to determine if we should drilldown
       function hasDrilldownData(newMapId) {
         var drillDownCountries = listOfStates.map(function(curVal) {
           return 'ind_' + curVal;
         });
         for (var i = 0; i < drillDownCountries.length; i++) {
           if (newMapId === drillDownCountries[i])
             return true;
         }
        
         return false;
       }
        
       // update colorindex for assigning random colors
       function colorIndexCheck() {
         if (colorIndex >= arrayOfColors.length) {
           colorIndex = 0;
         }
       }
          </script>
    
</body>

</html>