$(document).ready(function () {

    if($("#paid").is(":checked")){
        $(".admissionInput").prop('disabled',false);
    }else{
        $(".admissionInput").prop('disabled',true);

    }

    $("#parent-category").click(function () {
        $(this).removeClass('border-secondary');
        $(this).addClass('border-primary');


    })
    $("#parent-category").focusout(function () {
        $(this).addClass('border-secondary');
        $(this).removeClass('border-primary');

    });

    $("#search").click(function () {

        AddSearcGreenBorder($(this));
    });
    $("#search").focusout(function () {
        RemoveSearchGreenBorder($(this));
    });

    $(".featurelist-box").on('click', "#search", function () {
        AddSearcGreenBorder($(this));
    });

    $(".featurelist-box").on('focusout', "#search", function () {
        RemoveSearchGreenBorder($(this));
    });

//Input Ticket Amount
$("#ticket_amount").on('input', function(){
    const value = $(this).val();
    $(this).val(Math.abs(value));
});

//click to free or paid radio input
$("#free, #paid").on('click', function(){
    if($(this).val()==1){
        $(".admissionInput").prop('disabled',false);
    }else{
        $(".admissionInput").prop('disabled',true);


    }
});

    function getSubcategories(type) {
        $.ajax({
            url: categorylist,
            method: 'get',
            data: {
                'type': type
            },
            beforeSend: function () {
                $("#categoriesloader").removeClass('d-none');
            },
            success: function (data) {
                $("#categoriesloader").addClass('d-none');

                $("#categorieslist").html("");
                $(data.data).each(function (idx, result) {

                    var category = result.name;
                    var categories = result;
                    if (result.special_category == 1) {
                        category += " (Seasonal Category)";
                    }
                    var category_id = result.id;
                    var $html =
                        "<div class='collapse-box d-flex justify-content-between mb-3'><span>" +
                        category +
                        "</span><span><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down-fill' viewBox='0 0 16 16'> <path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/> </svg></span></div>";
                    if (type == "no-child") {
                        var cls0 = "no-child-categories-box";
                        // if (categories.active == 0) {
                        //     cls0 = "disable-no-child-categories-box";
                        // }
                        $html =
                            "<div class='no-child-collapse " + cls0 + " d-flex justify-content-between mb-3' category_id='" +
                            category_id + "'><span>" +
                            category + "</span></div>";

                    }


                    var html1 = '';
                    if (result.subcategories.length != 0) {
                        $(result.subcategories).each(function (index, result) {
                            var active_subcategory;
                            var cls = 'sub-box';
                            if (result.parkcategory.length > 0) {
                                active_subcategory = result.parkcategory[0].active;
                            }

                            // if (active_subcategory == 0 || result.active == 0 || categories.active == 0) {
                            //     cls = "disable-sub-box";
                            // }
                            let subcategory = result.name;
                            html1 += "<span class='" + cls + "'  value='" +
                                result.id + "' parentcategory='" + category +
                                "' parentcategory_id = '" + category_id +
                                "'><span>" + subcategory +
                                "</span></span>";
                        })
                        $html +=
                            "<div class='subcategories-box mt-4 mx-1 row d-flex text-center d-none'>" +
                            html1 + "</div>";
                    } else {
                        $html +=
                            "<div class='subcategories-box mx-1 row d-flex text-center d-none' style='position: relative; bottom: 5px;'><span class='text-secondary msg'>There are no child category added yet !</span> </div>";
                    }
                    $("#categorieslist").append($html);
                })

            },
            complete: function () {

                //if no child(stand alone) already  selected inside the saved categories when change the drop down value
                if (type == 'no-child') {
                    $(".saved-subcategories .no-child-categories .sub-box-selected").each(
                        function (idx, element) {
                            var no_child_category_id = $(this).find('.no-child-close-btn')
                                .attr('child_category_id');

                            var active = $(this).find('.no-child-close-btn')
                                .attr('status-active');

                            if (active == 1) {
                                $("#categorieslist").find("[category_id=" + no_child_category_id + "]").addClass('no-child-categories-box-selected');
                            }
                            $("#categorieslist").find("[category_id=" +
                                no_child_category_id + "]").removeClass(
                                    'no-child-categories-box');
                        })
                } else {
                    defaultSelectedCategories();
                }
            }


        })

    }


    var type = $("#parent-category :selected").val();
    getSubcategories(type);

    $("#parent-category").on('change', function () {

        let type = $(this).val();
        getSubcategories(type);
    });



    // categorieslist
    function defaultSelectedCategories() {
        $(".saved-subcategories .sub-box-selected").each(function (
            indx, element) {


            var value = $(this).find("[subcategory_id]").attr('subcategory_id');
            var subcategory = $("#categorieslist .subcategories-box").find('[value=' + value + ']');
            var active = $(this).find("[subcategory_id]").attr('active-status');

            if (active == 0) {

                subcategory.addClass('disable-sub-box');
                subcategory.removeClass('sub-box-selected');
            } else {
                subcategory.removeClass('disable-sub-box');
                subcategory.addClass('sub-box-selected');

            }
            subcategory.removeClass('sub-box');

        })
    }
    $("#categorieslist").on("click", ".collapse-box", function () {
        var index = $(this).index();
        var self = $(this);
        var subcategoriesBox = $(this).parent().find('div').eq(index + 1);

        subcategoriesBox.find(".msg").parent().css('visibility', 'visible');

        $(this).parent().find(".subcategories-box").each(function (indx, element) {
            $(this).parent().find('div').eq($(this).index() - 1).removeClass('green-border');

            if (!$(this).hasClass('d-none')) {
                var idx = $(this).index();
                if (subcategoriesBox.index() != $(this).index()) {
                    $(this).addClass('d-none');
                    RemoveUpCarretSymbol($(this).parent().find('div').eq(idx - 1))
                }
            }
        })

        // subcategoriesBox.addClass('d-none');
        if (subcategoriesBox.hasClass('subcategories-box')) {
            if (subcategoriesBox.hasClass('d-none')) {
                subcategoriesBox.removeClass('d-none');
                RemoveDownCarretSymbol($(this));
            } else {
                subcategoriesBox.addClass('d-none');
                RemoveUpCarretSymbol($(this));
            }
        }
        defaultSelectedCategories();
        $(this).addClass('green-border');
    })


    $("#categorieslist").on("click", ".no-child-categories-box", function () {
        var category_id = $(this).attr("category_id");
        var no_child_category = $(this).text();


        var no_child_group = $(".saved-subcategories .no-child-categories").hasClass(
            'no-child-categories');


        if (no_child_group) {
            var html =
                "<div class='sub-box-selected' style='cursor: default'> <span>" +
                no_child_category +
                "</span> <input type='hidden' name='category_ids[]' value='" +
                category_id +
                "'><img src='" + close_btn_icon + "' draggable='false' ondragstart='return false;' child_category_id='" +
                category_id + "'  class='no-child-close-btn'> </div>";
            $(".saved-subcategories .no-child-categories .row").append(html);
        } else {
            var html =
                "<div class='border border rounded mb-3 no-child-categories'><div class='text-left  mt-3 ml-3'> <label class='text-muted'>Standalone</label></div> <div class='row d-flex justify-content-start pl-4'> <div class='sub-box-selected' style='cursor: default'> <span>" +
                no_child_category +
                "</span> <input type='hidden' name='category_ids[]' value='" +
                category_id +
                "'><img src='" + close_btn_icon + "' draggable='false' ondragstart='return false;' child_category_id='" +
                category_id + "' class='no-child-close-btn'> </div> </div> </div>";
            $(".saved-subcategories ").append(html);
        }
        $(this).removeClass('no-child-categories-box');
        $(this).addClass('no-child-categories-box-selected');
    });


    $("#categorieslist").on("click", ".no-child-categories-box-selected", function () {
        // alert("working");
        var category_id = $(this).attr("category_id");
        var not_child_category_id = $('.saved-subcategories .no-child-categories').find(
            "[child_category_id=" + category_id + "]").parent().parent().children().length;
        if (not_child_category_id == 1) {
            $('.saved-subcategories .no-child-categories').find("[child_category_id=" +
                category_id + "]").parent().parent().parent().remove();

        } else {
            $('.saved-subcategories .no-child-categories').find("[child_category_id=" +
                category_id + "]").parent().remove();
        }

        $(this).addClass('no-child-categories-box');
        $(this).removeClass('no-child-categories-box-selected');

    });



    $("#categorieslist").on("click", ".sub-box", function () {
        $(this).removeClass('sub-box');
        $(this).addClass('sub-box-selected');
        var subcategory = $(this).text();
        var indexVal = $(this).index();
        var parentIndexVal = $(this).parent().index();
        var parentcategory = $(this).attr('parentcategory');
        var parentcategory_id = $(this).attr('parentcategory_id');
        var subcategory_id = $(this).attr('value');
        var parent_group = $(".saved-subcategories .subcategories .row").is("#parent_" +
            parentcategory_id);


        if (parent_group) {
            var html =
                "<div class='sub-box-selected' style='cursor: default'> <span>" + subcategory +
                "</span> <input type='hidden' name='subcategory_ids[]' value='" +
                subcategory_id +
                "'><img src='" + close_btn_icon + "' draggable='false' ondragstart='return false;' subcategory_id='" +
                subcategory_id + "' class='close-btn' index='" +
                indexVal +
                "' parentIdx='" + parentIndexVal + "'> </div>";
            $(".saved-subcategories #parent_" + parentcategory_id).append(html);

        } else {
            var html =
                "<div class='border border rounded mb-3 subcategories'><div class='text-left  mt-3 ml-3'> <label class='text-muted'>" +
                parentcategory +
                "</label><input type='hidden' value='" +
                parentcategory_id +
                "' name='category_ids[]'> </div> <div class='row d-flex justify-content-start pl-4' id='parent_" +
                parentcategory_id +
                "'> <div class='sub-box-selected' style='cursor: default'> <span>" + subcategory +
                "</span> <input type='hidden' name='subcategory_ids[]' value='" +
                subcategory_id +
                "'><img src='" + close_btn_icon + "' draggable='false' subcategory_id='" +
                subcategory_id + "' ondragstart='return false;' class='close-btn' index='" +
                indexVal +
                "' parentIdx='" + parentIndexVal + "'> </div> </div> </div>";

            $(".saved-subcategories ").append(html);
        }



    })

    $("#categorieslist").on("click", ".sub-box-selected", function () {

        let value = $(this).attr('value');
        var subcategoris = $(".saved-subcategories .subcategories").find('[subcategory_id=' +
            value + ']').parent().parent().children().length;

        if (subcategoris == 1) {
            $(".saved-subcategories .subcategories").find('[subcategory_id=' + value + ']').parent()
                .parent().parent().remove();
        } else {
            $(".saved-subcategories .subcategories").find('[subcategory_id=' + value + ']').parent()
                .remove();
        }
        $(this).addClass('sub-box');
        $(this).removeClass('sub-box-selected');

    });


    $(".saved-subcategories").on("click", ".close-btn", function () {
        var allSubcategory = $(this).parent().parent().children().length

        var parentIndex = $(this).attr('parentIdx');
        var index = $(this).attr('index');
        var value = $(this).parent().find('input').attr("value");
        var subcategory = $("#categorieslist").find('div').eq(parentIndex).find('[value=' + value +
            ']');

        subcategory.addClass('sub-box');
        subcategory.removeClass('sub-box-selected');

        if (allSubcategory == 1) {
            $(this).parent().parent().parent().remove();

        }

        $(this).parent().remove();

    })

    $(".saved-subcategories").on("click", ".no-child-close-btn", function () {
        // alert("Working");
        var not_child_category_id = $(this).parent().find('input').val();
        var all_no_child_categories = $(this).parent().parent().children().length;

        if (all_no_child_categories == 1) {
            $(this).parent().parent().parent().remove();
        } else {
            $(this).parent().remove();
        }
        var active = $(this).attr('status-active');
        $("#categorieslist").find("[category_id=" + not_child_category_id + "]").removeClass(
            'no-child-categories-box-selected').addClass('no-child-categories-box');

        if (active == 1) {
            $("#categorieslist").find("[category_id=" + not_child_category_id + "]").addClass('no-child-categories-box');
            // $("#categorieslist").find("[category_id=" + not_child_category_id + "]").addClass('no-child-categories-box-selected');
        }

    })

    //search filter

    $("#search").on("keyup", function () {
        var selectedVal = $("#parent-category :selected").val();

        var value = $(this).val().toLowerCase();
        if (selectedVal == 'no-child') {
            $("#categorieslist .no-child-collapse span").filter(function () {
                let checkSearch = $(this).text().toLowerCase().indexOf(value) > -1;
                if (checkSearch == false) {
                    $(this).parent(checkSearch).addClass('d-none');

                } else {
                    $(this).parent(checkSearch).removeClass('d-none');

                }

                $(this).toggle(checkSearch);

            })
        } else if (selectedVal == 'parent') {

            $("#categorieslist *").filter(function () {

                var checkSearch = $(this).text().toLowerCase().indexOf(value) > -1;
                if (checkSearch == false) {
                    $(this).toggle(checkSearch).addClass('d-none');
                    // $(".subcategories-box").removeClass('d-none');
                    $("#categorieslist").find(".msg").parent().css('visibility', 'hidden');
                    $("#categorieslist").find(".msg").parent().addClass('d-none');


                } else {
                    $(this).toggle(checkSearch).removeClass('d-none');
                    // $(".subcategories-box").addClass('d-none');

                }

            })
            if (value == '') {
                $(".subcategories-box").addClass('d-none');
                var up_symbol = $("#categorieslist").find(".bi-caret-up-fill");
                up_symbol.parent().append("<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down-fill' viewBox='0 0 16 16'> <path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'></path> </svg>");
                up_symbol.remove();


            }
        }




    })

    $(".featurelist-box #search").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        // $("#featurelist").find('.msg').remove();
        var total_div = $("#featurelist").children('.featurelist-collapse-box').length;

        $("#featurelist *").filter(function () {

            var checkSearch = $(this).text().toLowerCase().indexOf(value) > -1;
            if (checkSearch == false) {
                $(this).toggle(checkSearch).addClass('d-none');

                // $(".feature-box").removeClass('d-none');
                $("#featurelist").find(".msg").parent().css('visibility', 'hidden');
                $("#featurelist").find(".msg").parent().addClass('d-none');


            } else {
                // $(".feature-box").addClass('d-none');
                $(this).toggle(checkSearch).removeClass('d-none');
                // $("#featurelist").find(".msg").css("visibility","hidden");
                // $("#featurelist").find(".msg").parent().addClass('d-none');



            }

        })
        if (value == '') {
            $(".feature-box").addClass('d-none');

            var up_symbol = $("#featurelist").find(".bi-caret-up-fill");
            up_symbol.parent().append("<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down-fill' viewBox='0 0 16 16'> <path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'></path> </svg>");
            up_symbol.remove();




        }
    })

    // ---------Feature js------
    $("#featurelist").on("click", ".featurelist-collapse-box", function () {
        var index = $(this).index();
        var feature_box = $(this).parent().find('div').eq(index + 1);
        var child_features = feature_box.find('[feature_type]').length;

        feature_box.find(".msg").parent().css('visibility', 'visible');
        // feature_box.find(".msg").parent().removeClass('d-none');

        $(this).parent().find(".feature-box").each(function (index, element) {
            $(this).parent().find('div').eq($(this).index() - 1).removeClass('green-border');
            if (!$(this).hasClass('d-none')) {
                var idx = $(this).index();
                if (feature_box.index() != $(this).index()) {
                    $(this).addClass('d-none');
                    RemoveUpCarretSymbol($(this).parent().find('div').eq(idx - 1));
                }
            }
        });
        // if (child_features == 0) {
        //     feature_box.html('');
        //     feature_box.append("<span class='text-secondary msg' style='position:relative; bottom:10px;'>There are no child feature added yet !</span>");

        // }

        if (feature_box.hasClass('feature-box')) {
            if (feature_box.hasClass('d-none')) {
                feature_box.removeClass('d-none');
                RemoveDownCarretSymbol($(this));

            } else {
                feature_box.addClass('d-none');
                RemoveUpCarretSymbol($(this));
            }
        }

        $(this).addClass('green-border');

    })

    $(document).mouseup(function (e) {
        var selector = $("#featurelist").find(".featurelist-collapse-box");

        if (!selector.is(e.target) && selector.has(e.target).length === 0) {
            selector.removeClass('green-border');
        }
    });

    $(document).mouseup(function (e) {
        var selector = $("#categorieslist").find(".collapse-box");

        if (!selector.is(e.target) && selector.has(e.target).length === 0) {
            selector.removeClass('green-border');
        }
    });



    $("#featurelist").on("click", ".sub-box", function () {

        var feature = $(this).text();
        var feature_id = $(this).attr("value");
        var indexVal = $(this).index();
        var parentIndexVal = $(this).parent().index();
        var feature_type = $(this).attr('feature_type');
        var feature_type_id = $(this).attr('featuretype_id');

        var label_div = $(".saved-subfeatures .selectedfeatures .row").is("#feature_type_" + feature_type_id);
        if (label_div == false) {
            var html = "<div class='saved-subfeatures'><div class='border border rounded mb-3 selectedfeatures'> <div class='text-left mt-3 ml-3'><label class='text-muted'>" + feature_type + "</label> <input type='hidden' value='" + feature_type_id + "' name='feature_type_ids[]'> </div> <div class='row d-flex justify-content-start pl-4' id='feature_type_" + feature_type_id + "'> <div class='sub-box-selected' style='cursor: default'> <span>" + feature + "</span> <input type='hidden' name='feature_ids[]' value='" + feature_id + "'> <img src='" + close_btn_icon + "' draggable='false' feature_id='" + feature_id + "' ondragstart='return false;' class='close-btn' index='" + indexVal + "' parentidx='" + parentIndexVal + "'> </div>  </div> </div></div>";
            $(".savedfeatures-box").append(html);

        } else {
            var html = "<div class='sub-box-selected' style='cursor: default'> <span>" + feature + "</span> <input type='hidden' name='feature_ids[]' value='" + feature_id + "'> <img src='" + close_btn_icon + "' draggable='false' feature_id='" + feature_id + "' ondragstart='return false;' class='close-btn' index='" + indexVal + "' parentidx='" + parentIndexVal + "'> </div>";
            $(".savedfeatures-box #feature_type_" + feature_type_id).append(html);
        }



        $(this).removeClass('sub-box');
        $(this).addClass('sub-box-selected');

    })
    $("#featurelist").on("click", ".sub-box-selected", function () {
        var feature_id = $(this).attr("value");
        var feature = $(".savedfeatures-box").find("[feature_id=" + feature_id + "]");
        var allfeatures = feature.parent().parent().children().length;
        featureClose(allfeatures, feature, $(this));
    })

    $(".savedfeatures-box").on("click", ".close-btn", function () {
        var allfeatures = $(this).parent().parent().children().length;
        var feature_id = $(this).attr('feature_id');
        var feature = $("#featurelist").find("[value=" + feature_id + "]");
        featureClose(allfeatures, $(this), feature);
    })

    function featureClose(allfeatures, selectdfeature, feature) {
        if (allfeatures == 1) {
            selectdfeature.parent().parent().parent().parent().remove();
        } else {
            selectdfeature.parent().remove();
        }
        active_child_feature = selectdfeature.attr('active-feature');
        if(active_child_feature==0){

            feature.removeClass('sub-box-selected');
        }else{
            feature.removeClass('sub-box-selected');
            feature.addClass('sub-box');


        }

    }

    $(".savedfeatures-box .sub-box-selected").each(function (index, element) {

        var feature_id = $(this).find("[feature_id]").attr('feature_id');

        var feature = $("#featurelist").find("[value=" + feature_id + "]");
        // var subcategory = $("#categorieslist .subcategories-box").find('[value=' + value + ']');

        if(feature.hasClass('disable-sub-box')){
            feature.removeClass('sub-box');
            feature.removeClass('sub-box-selected');


        }else{
            feature.addClass('sub-box-selected');
            feature.removeClass('sub-box');
        }

    })

    function AddSearcGreenBorder(selector) {
        selector.parent().removeClass('border-secondary');
        selector.parent().addClass('border-primary');
    }
    function RemoveSearchGreenBorder(selector) {
        selector.parent().addClass('border-secondary');
        selector.parent().removeClass('border-primary');
    }

    function RemoveDownCarretSymbol(selector) {
        selector.find('.bi-caret-down-fill').remove();
        selector.find('span:nth-child(2)').append("<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-up-fill' viewBox='0 0 16 16'> <path d='m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z'></path> </svg>");

    }

    function RemoveUpCarretSymbol(selector) {
        selector.find(".bi-caret-up-fill").remove();
        selector.find('span:nth-child(2)').append("<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down-fill' viewBox='0 0 16 16'> <path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'></path> </svg>");
    }
})
