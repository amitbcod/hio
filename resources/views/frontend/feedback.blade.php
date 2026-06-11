@extends('layouts.app')

@section('content')

<style>
     .container {
        max-width: 1300px;
    }
    .feedback-head {
      font-weight: bold;
      margin-bottom: 28px;
      padding-top:30px
    }
 </style>



<div class="container">
  <h2 class="feedback-head">Feedback for Trip #{{ $trip->id }}</h2>

  @if($review)
    <div class="alert alert-info">
      You have previously submitted feedback for this trip. You can update it below.
    </div>
  @endif

  @php
    $tripCriteria = optional($tripReviewItem)->criteria ?? [];
    $tripReviewData = [];
    if ($review && $review->overall_review) {
      $decodedTripReview = json_decode($review->overall_review, true);
      if (is_array($decodedTripReview)) {
        $tripReviewData = $decodedTripReview;
      } else {
        $tripReviewData = [
          'hear_about_us' => '',
          'trip_comments' => $review->overall_review,
        ];
      }
    }

    $tripRatingFields = [
      'communication' => 'Communication',
      'booking_service' => 'Booking Service',
      'travel_consulting' => 'Travel Consulting',
      'on_destination' => 'On Destination',
      'post_booking' => 'Post Booking',
    ];

    $accommodationRatingFields = [
      'service_quality' => 'Service Quality',
      'cleanliness' => 'Cleanliness',
      'food' => 'Food',
      'staff' => 'Staff',
      'overall_tour_experience' => 'Overall tour experience',
    ];

    $activityRatingFields = [
      'equipment' => 'Equipment',
      'tour_guide' => 'Tour Guide',
      'safety' => 'Safety',
      'staff' => 'Staff',
      'overall_tour_experience' => 'Overall tour experience',
    ];
  @endphp

  <style>
    .rating-grid {
      display: grid;
      gap: 12px;
      margin-bottom: 24px;
    }

    .rating-row {
      display: grid;
      grid-template-columns: 210px 1fr;
      align-items: center;
      gap: 12px;
      padding: 6px 0;
      border-bottom: 1px solid #ececec;
    }

    .rating-row:last-child {
      border-bottom: none;
    }

    .rating-label {
      font-weight: 600;
    }

    .rating-legend {
      display: grid;
      grid-template-columns: 220px repeat(6, minmax(80px, 1fr));
      gap: 12px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 8px;
      padding: 8px 0;
      border-bottom: 1px solid #ccc;
      background: #19b5b5;
    color: #fff;
    }

    .star-rating {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .stars {
      display: flex;
      cursor: pointer;
      display: grid;
      gap: 12px;
      cursor: pointer;
      grid-template-columns: repeat(5, minmax(158px, 1fr));
    }

    .star {
      font-size: 26px;
      color: #ddd;
      transition: color 0.2s ease;
      text-align: center;
    }

    .star.filled {
      color: #ffc107;
    }

    .rating-value {
      min-width: 40px;
      text-align: left;
      color: #555;
    }

    .section-box {
      border: 1px solid #ddd;
      padding: 16px;
      margin-bottom: 24px;
      background: #fff;
    }

    .section-box h2 {
      margin-top: 0;
    }

    .section-comment {
      margin-top: 16px;
    }

    .section-comment textarea {
      width: 100%;
      min-height: 90px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      resize: vertical;
    }

    .section-box h2 {
      color: #19b5b5;
      font-weight: bold;
      font-size: 25px;
      margin-bottom: 20px;
    }

    .feedback-btn-div {
        margin-bottom: 30px;
    }

    .feedback-btn-div .btn-primary {
        background: #139999;
        color:#ffffff;
        border-color: #139999;
        font-weight: 600;
    }
    .rating-legend > div {
      text-align: center;
    }

    @media (max-width:767px) {
      .rating-legend {
        grid-template-columns: 110px repeat(6, minmax(30px, 1fr));
        gap: 5px;
      }
      .rating-row {
        grid-template-columns: 100px 1fr;
        gap: 5px;
      }
      .stars {
          gap: 5px;
          grid-template-columns: repeat(5, minmax(30px, 1fr));
      }
      .rating-label {
          font-size: 13px;
      }
      .rating-legend > div {
          text-align: center;
          font-size: 11px;
      }
      .section-box {
        border: 0px solid #ddd;
        padding: 0;
      }
    }
  </style>

  <form method="post" action="{{ route('frontend.feedback.submit', [$trip->id]) }}">
    @csrf

    <div class="section-box">
      <h2>Overall Trip Review</h2>

      <div class="rating-legend">
        <div></div>
        <div>Poor (1)</div>
        <div>Average (2)</div>
        <div>Good (3)</div>
        <div>Very Good (4)</div>
        <div>Excellent (5)</div>
      </div>
      
      <div style="border: 1px solid #eee; padding: 12px; margin-bottom: 18px; background: #f9f9f9;">
        @foreach($tripRatingFields as $fieldKey => $fieldLabel)
          <div class="rating-row">
            <div class="rating-label">{{ $fieldLabel }}</div>
            <div class="star-rating">
              <div class="stars" data-rating-input="trip[{{ $fieldKey }}]">
                @for($i = 1; $i <= 5; $i++)
                  <span class="star" data-value="{{ $i }}">★</span>
                @endfor
              </div>
              <span class="rating-value">-</span>
            </div>
            <input type="hidden" name="trip[{{ $fieldKey }}]" value="{{ $tripCriteria[$fieldKey] ?? '' }}">
          </div>
        @endforeach
      

        <div class="rating-row">
          <div class="rating-label">Overall tour experience</div>
          <div class="star-rating">
            <div class="stars" data-rating-input="overall_rating">
              @for($i = 1; $i <= 5; $i++)
                <span class="star" data-value="{{ $i }}">★</span>
              @endfor
            </div>
            <span class="rating-value" id="overall_rating_display">-</span>
          </div>
          <input type="hidden" name="overall_rating" id="overall_rating" value="{{ $review->overall_rating ?? '' }}">
        </div>

        <div class="section-comment">
          <label><strong>How did you hear about us?</strong></label>
          <textarea name="hear_about_us">{{ $tripReviewData['hear_about_us'] ?? '' }}</textarea>
        </div>

        <div class="section-comment">
          <label><strong>Any other feedback and comments:</strong></label>
          <textarea name="trip_comments">{{ $tripReviewData['trip_comments'] ?? '' }}</textarea>
        </div>
      </div>
    </div>

    @if($trip->accommodationBookings && $trip->accommodationBookings->count())
      <div class="section-box">
        <h2>Accommodation Reviews</h2>
        <div class="rating-legend">
          <div></div>
          <div>Poor (1)</div>
          <div>Average (2)</div>
          <div>Good (3)</div>
          <div>Very Good (4)</div>
          <div>Excellent (5)</div>
        </div>

        @foreach($trip->accommodationBookings as $ab)
          @php
            $accReview = $accommodationReviews->get($ab->id);
            $accCriteria = optional($accReview)->criteria ?: [];
          @endphp

          <div style="border: 1px solid #eee; padding: 12px; margin-bottom: 18px;background: #f9f9f9;">
            <h3 style="margin-top: 0;">{{ $ab->property_name ?? ('Accommodation #' . $ab->id) }}</h3>
            <input type="hidden" name="accommodations[{{ $ab->id }}][id]" value="{{ $ab->id }}">

            @foreach($accommodationRatingFields as $fieldKey => $fieldLabel)
              <div class="rating-row">
                <div class="rating-label">{{ $fieldLabel }}</div>
                <div class="star-rating">
                  <div class="stars" data-rating-input="accommodations[{{ $ab->id }}][{{ $fieldKey }}]">
                    @for($i = 1; $i <= 5; $i++)
                      <span class="star" data-value="{{ $i }}">★</span>
                    @endfor
                  </div>
                  <span class="rating-value">-</span>
                </div>
                <input type="hidden" name="accommodations[{{ $ab->id }}][{{ $fieldKey }}]" value="{{ $accCriteria[$fieldKey] ?? '' }}">
              </div>
            @endforeach

            <div class="section-comment">
              <label><strong>Any other feedback and comments:</strong></label>
              <textarea name="accommodations[{{ $ab->id }}][review]">{{ $accReview->review ?? '' }}</textarea>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    @if($trip->activityBookings && $trip->activityBookings->count())
      <div class="section-box">
        <h2>Activity Reviews</h2>
        <div class="rating-legend">
          <div></div>
          <div>Poor (1)</div>
          <div>Average (2)</div>
          <div>Good (3)</div>
          <div>Very Good (4)</div>
          <div>Excellent (5)</div>
        </div>

        @foreach($trip->activityBookings as $act)
          @php
            $actReview = $activityReviews->get($act->id);
            $actCriteria = optional($actReview)->criteria ?: [];
          @endphp

          <div style="border: 1px solid #eee; padding: 12px; margin-bottom: 18px;background: #f9f9f9;">
            <h3 style="margin-top: 0;">{{ $act->activity_name ?? ('Activity #' . $act->id) }}</h3>
            <input type="hidden" name="activities[{{ $act->id }}][id]" value="{{ $act->id }}">

            @foreach($activityRatingFields as $fieldKey => $fieldLabel)
              <div class="rating-row">
                <div class="rating-label">{{ $fieldLabel }}</div>
                <div class="star-rating">
                  <div class="stars" data-rating-input="activities[{{ $act->id }}][{{ $fieldKey }}]">
                    @for($i = 1; $i <= 5; $i++)
                      <span class="star" data-value="{{ $i }}">★</span>
                    @endfor
                  </div>
                  <span class="rating-value">-</span>
                </div>
                <input type="hidden" name="activities[{{ $act->id }}][{{ $fieldKey }}]" value="{{ $actCriteria[$fieldKey] ?? '' }}">
              </div>
            @endforeach

            <div class="section-comment">
              <label><strong>Any other feedback and comments:</strong></label>
              <textarea name="activities[{{ $act->id }}][review]">{{ $actReview->review ?? '' }}</textarea>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <div class="feedback-btn-div">
      <button class="btn btn-primary" type="submit">{{ $review ? 'Update feedback' : 'Submit feedback' }}</button>
    </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const starContainers = document.querySelectorAll('.stars');

      starContainers.forEach(container => {
        const inputName = container.getAttribute('data-rating-input');
        const stars = container.querySelectorAll('.star');
        const inputElement = document.querySelector(`input[name="${inputName}"]`);
        const ratingDisplay = container.parentElement.querySelector('.rating-value');

        if (!inputElement) {
          return;
        }

        if (inputElement.value) {
          updateStars(container, inputElement.value);
          if (ratingDisplay) {
            ratingDisplay.textContent = inputElement.value + '★';
          }
        }

        stars.forEach(star => {
          star.addEventListener('mouseenter', function() {
            const value = this.getAttribute('data-value');
            updateStars(container, value);
            if (ratingDisplay) {
              ratingDisplay.textContent = value + '★';
            }
          });
        });

        container.addEventListener('mouseleave', function() {
          if (inputElement.value) {
            updateStars(container, inputElement.value);
            if (ratingDisplay) {
              ratingDisplay.textContent = inputElement.value + '★';
            }
          } else {
            stars.forEach(s => s.classList.remove('filled'));
            if (ratingDisplay) {
              ratingDisplay.textContent = '-';
            }
          }
        });

        stars.forEach(star => {
          star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            inputElement.value = value;
            updateStars(container, value);
            if (ratingDisplay) {
              ratingDisplay.textContent = value + '★';
            }
          });
        });
      });

      function updateStars(container, rating) {
        const stars = container.querySelectorAll('.star');
        stars.forEach(star => {
          const value = star.getAttribute('data-value');
          if (value <= rating) {
            star.classList.add('filled');
          } else {
            star.classList.remove('filled');
          }
        });
      }
    });
  </script>
</div>
@endsection
