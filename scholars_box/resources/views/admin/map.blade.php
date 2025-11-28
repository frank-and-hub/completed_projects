<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>ZingSoft Demo</title>
    @php  

    
        $states = \App\Models\CountryData\State::withCount([
            'users',
            'users as male_count' => function ($query) {
                $query->where('gender', 'male');
            },
            'users as female_count' => function ($query) {
                $query->where('gender', 'female');
            },
        ])->get();
       
      
    @endphp

    <script src="{{ asset('admin/js/zingchart.min.js') }}"></script>

    <style>
        html,
        body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        #myChart {
            height: 100%;
            width: 100%;
            min-height: 150px;
        }

        .zc-ref {
            display: none;
        }
    </style>
</head>

<body>
    <div id='myChart'></div>  
</body>

<script>
    ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "b55b025e438fa8a98e32482b5f768ff5"];
    let cityResults = [
@foreach ($states as $state)
{
    'name': @json($state->name === 'TELANGANA' ? 'TS' : $state->abbreviation),
    'statename': @json($state->name),
    'studentcount': @json($state->users_count),
    'male': @json($state->male_count),
    'female': @json($state->female_count),
},
@endforeach


    ];

  console.log(cityResults,'cityResults');
    // must load maps and first map we are going to render
    zingchart.loadModules('maps,maps-ind');

    var colorGradient = ['#5660D9'];
    var colorIndex = 0;
    var listOfStates = ["gj", "ka"];


    /* 
     *  need this function to render the first map load with random colors
     */
    var objectStates = function(arrayOfStates) {
        var objectOfStates = {};
        for (var i = 0; i < arrayOfStates.length; i++) {
            var itemId = arrayOfStates[i].toUpperCase();
            objectOfStates[itemId] = {
                'background-color': 'red'
            }
        }

        return objectOfStates;
    }(listOfStates);

    console.log('---- object form of states ----', objectStates);
    ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "b55b025e438fa8a98e32482b5f768ff5"];


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
                        backgroundColor: 'red'
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

    function setResults() {
        let stateResults = {};

        cityResults.forEach(function(state) {

          
            let stateName = state.name;
            let colorIndex = state.studentcount;
            let female = state.female;
            let styleObject = {

                label: {
                    fontSize: '14px'
                },
                tooltip: {

                    text: 'State Name: ' + state.statename + '<br/>Student Count:' + state.studentcount +
                        '<br/>Male Count:' + state.male + '<br/>Female Count:' + female,
                    fontSize: '14px',
                    textAlign: 'left',
                    width: '200px',
                    wrapText: true
                }
            };
            console.log('styleObject',styleObject);
            stateResults[stateName] = styleObject;
        });

        return {
            backgroundColor: '#0D1427',
            gui: {
                watermark: {
                    position: 'tr'
                }
            },
            globals: {
                fontFamily: 'Open Sans Condensed',
                shadow: false
            },
            title: {
                backgroundColor: '#0D1427',
                color: '#333',
                fontSize: '24px',
                textAlign: 'left',
                x: '10px',
                y: '10px'
            },
            subtitle: {
                fontSize: '16px',
                color: '#333',
                textAlign: 'left',
                x: '10px',
                y: '40px'
            },
            labels: [{

                fontSize: '14px',
                paddingTop: '40px',
                y: '470px',
                x: '355px'
            }],
            legend: {
                backgroundColor: 'none',
                borderWidth: 0,
                offsetY: '-10px',
                toggleAction: 'none',
                verticalAlign: 'bottom',
                item: {
                    fontSize: '16px'
                },
                marker: {
                    type: 'rectangle',
                    width: '20px',
                    height: '10px',
                }
            },
            shapes: [ // render map
                {
                    type: 'zingchart.maps',
                    options: {
                        id: 'map',
                        name: 'ind',
                        scale: true,
                        y: '40px',
                        style: {
                            borderColor: '#FFF',
                            items: stateResults,
                            hoverState: {
                                alpha: 0.3,
                                backgroundColor: '#0D1427'
                            }
                        }
                    }
                }
            ]
        };
    }


    zingchart.render({
        id: 'myChart',
        data: setResults(),
        height: '100%',
        width: '100%'
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
                        backgroundColor: '#5660D9',
                        fontColor: '#fff',
                        hoverState: {
                            alpha: 0.3
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
                        },
                    }
                }
            }, { //Button to go back to main map.
                id: 'button',
                type: 'rectangle',
                height: 25,
                width: 150,
                x: 750,
                y: 20,

                backgroundColor: '#C4C4C4',
                borderRadius: 3,
                cursor: 'hand',
                hoverState: {
                    alpha: 0.3,
                    backgroundColor: 'white'
                },
                label: {
                    text: 'Click on Back to See all States',
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
                    alpha: 0.3
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
</script>

</html>
