@extends('frontend.layout')

@section('title', 'About Us | Holidays.io')
@section('meta_description', 'Learn more about Holidays.io and our travel services.')

@section('content')
    <section class="page-section">
        <div class="wrap">
            <h1>About Us - Lolotte Rental and Tours</h1>
            <p>Lolotte Rental and Tours Ltd (LRT) proudly serves as the local partner for Holidays.io (HIO) in Mauritius and
                Rodrigues. With over three decades of experience in the tourism industry, the founder of LRT has a deep
                passion for her homeland. Her profound love for the islands, respect for nature, and commitment to
                preserving the unique cultural and environmental heritage of Mauritius are the cornerstones of her company.
                LRT is your local connection!</p>

                <div class="inner-section split-highlight">
                    <div class="highlight-copy">
                        <h3>Holidays Services</h3>
                        <p>
                            Transport Preferences: Choose your ideal ride and travel in style, your way!<br>
Transfers: Enjoy seamless transfers from the airport to your hotel for a stress-free start to your holiday.<br>
Accommodation: Relax in accommodations that offer luxury, comfort, and exceed your expectations.<br>
Activities: Dive into a variety of fun activities designed for all ages and interests.<br>
Packaging: Discover our exciting holiday packages that make your dream vacation come true.<br>
Sustainable Holidays: Experience eco-friendly fun with The Rainbow IO's sustainable holiday options!
                        </p>

                    </div>
                    <div class="highlight-card">
                        <img src="{{ $heroSlides[0]['image'] ?? asset('images/services-mauritius.jpg') }}"
                            alt="Featured Mauritius experience">
                    </div>
                </div>
            

            <h2>Our Story</h2>
            <p>Dominique, the owner and operator of LRT, grew up immersed in the natural beauty of Mauritius. Her intimate
                connection with the island’s landscapes is the driving force behind LRT’s dedication to providing
                personalised, nature-centric tourism experiences. LRT stands by its guests 24/7, ensuring that every visitor
                is cared for and experiences the true essence of Mauritius and Rodrigues.</p>

            <h2>Our Mission: Connecting Nature and People</h2>

            <p>After 30 years in the Mauritian tourism industry, LRT joined forces with Holidays.io to extend its reach to a
                global audience of eco-conscious travellers. Our mission is simple: to offer authentic eco-tourism
                experiences that showcase the natural beauty of Mauritius and Rodrigues while promoting sustainability and
                environmental education among locals and visitors alike.</p>

            <p>Through our work, we create a positive impact by fostering a win-win situation for both travellers and the
                islands themselves. We aim to cultivate a deeper understanding of sustainability—benefiting both the
                environment and the people who call these islands home.</p>

            <h2>Caring for Our Destination</h2>

            <p>Over the years, LRT has cultivated strong relationships with local accommodation providers who share our
                commitment to eco-tourism and sustainable travel. We work closely with nature-conscious owners of bed and
                breakfasts, guest houses, beach villas, apartments, and lodges to offer eco-friendly stays across Mauritius
                and Rodrigues.</p>

            <p>LRT collaborates with luxury villas, boutique hotels, and other accommodation providers who share our vision
                of "Caring for the Destination." Our goal is to provide visitors with diverse accommodation options that
                align with our principles of sustainability and responsible travel.</p>

            <h2>Eco, Adventure, and Wellness Tourism</h2>

            <p>At LRT, we stay ahead of the curve by embracing emerging travel trends. We recognise that modern travellers
                are increasingly seeking experiences that combine wellness, culture, and adventure. Our tours and
                experiences are designed to reflect this shift, offering opportunities to connect with nature, explore local
                culture, and engage in sustainable travel practices.</p>

            <p>We believe that “sustainability” is not just about protecting natural environments—it is about preserving
                cultural heritage and ensuring tourism benefits local communities. By promoting responsible tourism, we aim
                to protect both the environmental and cultural richness of Mauritius and Rodrigues.</p>

            <h2>The Personal Touch</h2>

            <p>Our passion for Mauritius runs deep. At Lolotte Rental and Tours, we are dedicated to showing visitors the
                real Mauritius—beyond the tourist brochures and well-trodden paths. Our team goes the extra mile to
                introduce you to the island’s hidden gems, from unspoiled beaches to off-the-beaten-path natural wonders.
            </p>

            <p>Whether you want to explore the classic sights or discover hidden treasures, we’re here to make it happen. We
                handle every aspect of your journey—from transportation and accommodation to personalised tours—ensuring you
                have the best possible experience during your stay. Your comfort and enjoyment are our top priorities, and
                we won’t rest until you’ve experienced the magic of Mauritius firsthand.</p>

            <p>Let Lolotte Rental and Tours can show you a side of Mauritius you have never seen before.</p>

        </div>
    </section>
@endsection