@if ($rating == 1)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating == 2)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating == 3)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif($rating == 4)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating == 5)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>


    {{-- half star rating --}}
@elseif ($rating >= 1.1 && $rating < 2)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star-half"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating >= 2.1 && $rating < 3)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star-half"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating >= 3.1 && $rating < 4)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star-half"></i></small>
    <small class="rating-color"><i class="tf-icons bx bx-star"></i></small>
@elseif ($rating >= 4.1 && $rating < 5)
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star"></i></small>
    <small class="rating-color"><i class="tf-icons bx bxs-star-half"></i></small>
@endif
