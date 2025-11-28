<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Subcategory;
use App\Traits\CommonTraits;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaDataSeeder extends Seeder
{

    use CommonTraits;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->updateMetaDataInCategory();
        $this->updateSlugsInFeatures();
    }

    public function updateMetaDataInCategory()
    {

        $data = [
            [
                'name' => 'Adventure awaits',
                'title' => 'Adventure Parks Near You – Thrilling Outdoor Escapes | Parkscape',
                'description' => 'Ready for an adrenaline rush? Discover the best parks with zip lines, climbing walls, and wild terrain near you. Let the adventure begin!'
            ],
            [
                'name' => 'Amazing Attractions',
                'title' => 'Must-See Park Attractions Near You | Parkscape',
                'description' => 'From botanical gardens to historic landmarks, explore amazing park attractions close to home. Your next adventure is just around the corner.'
            ],
            [
                'name' => 'Cool Features',
                'title' => 'Cool Park Features Near You – Unique & Fun Elements | Parkscape',
                'description' => 'Discover parks with cool features like water fountains, art installations, and shaded canopies. Find hidden gems in your area today.'
            ],
            [
                'name' => 'Dining at the Park',
                'title' => 'Parks with Picnic Tables & Dining Spots Near You | Parkscape',
                'description' => 'Looking for parks where you can enjoy your meal outdoors? Discover nearby parks with picnic tables, shaded spots, and relaxing areas to eat with family or friends.'
            ],
            [
                'name' => 'Monumental Mountains',
                'title' => 'Mountain Parks Near You – Hike, Climb, and Explore | Parkscape',
                'description' => 'Reach new heights at parks with breathtaking mountain views, scenic trails, and fresh alpine air. Explore nature’s peaks near you.'
            ],
            [
                'name' => 'Picture Perfect Parks',
                'title' => 'Most Instagrammable Parks Near You – Snap Stunning Views | Parkscape',
                'description' => 'Capture beautiful backdrops and scenic moments at the most picture-perfect parks around. Ideal for photos, reels, and nature lovers.'
            ],
            [
                'name' => 'Play the Best',
                'title' => 'Best Playgrounds & Sports Parks Near You | Parkscape',
                'description' => 'Let the games begin! Explore top-rated parks for play and sports – from playgrounds to basketball courts, it’s all here.'
            ],
            [
                'name' => 'The Beauty of Nature',
                'title' => 'Nature Parks Near You – Relax, Reflect, Reconnect | Parkscape',
                'description' => 'Unwind in the beauty of nature. Explore peaceful parks with trees, lakes, trails, and wildlife—all near you.'
            ],
            [
                'name' => 'Winter Wonderlands',
                'title' => 'Snowy Parks & Winter Activities Near You | Parkscape',
                'description' => 'Enjoy frosty fun at parks that shine in winter—sledding hills, snow trails, and festive vibes await!'
            ],
            [
                'name' => 'Autumn Changes',
                'title' => 'Fall Foliage Parks Near You – Stunning Autumn Colors | Parkscape',
                'description' => 'See the leaves turn! Discover the most beautiful parks to enjoy crisp air, cozy walks, and vibrant fall colors.'
            ],
            [
                'name' => 'U.S. National Parks',
                'title' => 'Explore U.S. National Parks Near You – Nature’s Finest | Parkscape',
                'description' => 'From iconic landmarks to hidden wonders, discover U.S. national parks worth visiting. Experience nature’s grandeur near you.'
            ],
            [
                'name' => 'World Park Explorer',
                'title' => 'Explore Global Parks – Nature from Around the World | Parkscape',
                'description' => 'Travel through nature without a passport. Discover international parks and green spaces that inspire and amaze.'
            ],
            [
                'name' => "Rock 'n' Climbing",
                'title' => 'Climbing Parks Near You – Rock Walls & Adventure Routes | Parkscape',
                'description' => "Explore climbing parks with bouldering zones, rock walls, and vertical thrills. Challenge yourself in nature's ultimate playground."
            ],
            [
                'name' => "Awesome Hiking Trails",
                'title' => "Best Hiking Trails Near You – Scenic Routes & Outdoor Treks | Parkscape",
                'description' => "Lace up and hit the trails! Discover local parks with breathtaking hikes and nature-filled adventures."
            ],
            [
                'name' => "Thrilling Mountain Biking",
                'title' => "Mountain Biking Parks Near You – Ride the Trails | Parkscape",
                'description' => "Shred through rugged terrain and forested trails at top mountain biking parks near you. For beginners to pros!"
            ],
            [
                'name' => "Camping Under the Stars",
                'title' => "Campgrounds & Parks for Stargazing Near You | Parkscape",
                'description' => "Pitch your tent under the stars. Explore camping parks offering peaceful stays and night sky views."
            ],
            [
                'name' => "Roaring Water Rafting",
                'title' => "Whitewater Rafting Parks Near You – Ride the Rapids | Parkscape",
                'description' => "Get wet and wild with parks offering thrilling rafting experiences and riverside fun."
            ],
            [
                'name' => "Gone Fishing Again",
                'title' => "Fishing Parks Near You – Peaceful Lakes & Rivers | Parkscape",
                'description' => "Cast your line at serene fishing spots near you. Great for solo trips or family weekends."
            ],
            [
                'name' => "Refreshing Swimming Holes",
                'title' => "Swimming Holes Near You – Natural & Cool Escapes | Parkscape",
                'description' => "Cool off in natural swimming spots! Dive into local rivers, creeks, and springs."
            ],
            [
                'name' => "Transportation Themed Parks",
                'title' => "Transport-Themed Parks Near You – Trains, Planes & Fun | Parkscape",
                'description' => "All aboard for fun! Explore parks featuring trains, planes, and more for transport-loving visitors."
            ],
            [
                'name' => "Zoos & Aquariums",
                'title' => "Zoos & Aquariums Near You – Wildlife & Ocean Wonders | Parkscape",
                'description' => "From lions to jellyfish, explore nearby zoos and aquariums that inspire learning and fun."
            ],
            [
                'name' => "Amusement Rides & Games",
                'title' => "Amusement Parks Near You – Rides, Games & Thrills | Parkscape",
                'description' => "Feel the fun with classic rides and carnival games. A perfect day out for all ages!"
            ],
            [
                'name' => "Museums & Exhibits",
                'title' => "Parks with Museums & Exhibits Near You | Parkscape",
                'description' => "Blend nature and knowledge. Discover parks with interactive exhibits and cultural displays."
            ],
            [
                'name' => "Concerts & Shows",
                'title' => "Parks with Live Shows & Concerts Near You | Parkscape",
                'description' => "Enjoy open-air music and performances in scenic parks near you."
            ],
            [
                'name' => "RC Airplane Fields",
                'title' => "RC Airplane Parks Near You – Fly Your Wings! | Parkscape",
                'description' => "Love remote flying? Find parks with open spaces for RC airplane fun and hobby flights."
            ],
            [
                'name' => "Bocce Ball Courts",
                'title' => "Bocce Ball Parks Near You – Classic Outdoor Fun | Parkscape",
                'description' => "Relax and roll! Discover parks with bocce courts for all skill levels."
            ],
            [
                'name' => "Chess Tables",
                'title' => "Parks with Chess Tables Near You – Brain Meets Nature | Parkscape",
                'description' => "Enjoy a thoughtful game surrounded by nature. Find chess tables in peaceful park settings."
            ],
            [
                'name' => "Ping Pong Tables",
                'title' => "Table Tennis Parks Near You – Ping Pong Outdoors | Parkscape",
                'description' => "Play casual or competitive ping pong at local parks with public tables."
            ],
            [
                'name' => "Disc Golf Courses",
                'title' => "Disc Golf Parks Near You – Toss & Score Outdoors | Parkscape",
                'description' => "Grab your discs and explore the best disc golf courses in public parks."
            ],
            [
                'name' => "Street Hockey Rinks",
                'title' => "Street Hockey Parks Near You – Rinks for Quick Play | Parkscape",
                'description' => "Lace up and play at parks with street hockey courts and friendly match spaces."
            ],
            [
                'name' => "Skateboard Parks",
                'title' => "Skate Parks Near You – Ramps, Rails & Airtime | Parkscape",
                'description' => "Shred the best skateboarding spots near you. Great for tricks, grinds, and community vibes."
            ],
            [
                'name' => "Pump Tracks",
                'title' => "Pump Track Parks Near You – Ride, Flow, Repeat | Parkscape",
                'description' => "Feel the rhythm on pump tracks made for bikes, scooters, and fun momentum riding."
            ],
            [
                'name' => "Coffee & Desserts",
                'title' => "Parks with Coffee & Dessert Spots Near You | Parkscape",
                'description' => "Stroll and sip! Find parks with cozy cafés and sweet dessert treats nearby."
            ],
            [
                'name' => "Eateries & Bistros",
                'title' => "Dining Parks Near You – Eateries & Bistros in Nature | Parkscape",
                'description' => "Grab a bite surrounded by greenery. Discover relaxing parks with local eats and cafés."
            ],
            [
                'name' => "Quick Bites & Food Trucks",
                'title' => "Food Truck Parks Near You – Fast, Fresh, & Tasty | Parkscape",
                'description' => "Fuel up at parks with food trucks and quick snack stations. Great for foodies on the move!"
            ],
            [
                'name' => "Bars & Beer Gardens",
                'title' => "Beer Gardens & Park Bars Near You – Sip in the Sun | Parkscape",
                'description' => "Cheers to outdoor drinks! Find parks with beer gardens and casual bar setups."
            ],
            [
                'name' => "The Rockies",
                'title' => "Explore the Rockies – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Rockies with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "The Coast Mountains",
                'title' => "Explore The toast Mountains – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Coast Mountains with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "The Alps",
                'title' => "Explore the Alps – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Alps with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "The Dolomites",
                'title' => "Explore the Dolomites – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Dolomites with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "The Andes",
                'title' => "Explore the Andes – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Andes with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "The Himalayas",
                'title' => "Explore the Himalayas – Parks for Hikes, Views & Adventure | Parkscape",
                'description' => "Discover parks near the Himalayas with scenic trails, mountain views, and unforgettable outdoor moments."
            ],
            [
                'name' => "Exquisite Estates",
                'title' => "Estate Parks Near You – Historic Grounds & Grand Landscapes | Parkscape",
                'description' => "Stroll through elegant estate parks with gardens, paths, and timeless architecture."
            ],
            [
                'name' => "Graceful Fountains",
                'title' => "Parks with Fountains Near You – Calm, Cool & Classic | Parkscape",
                'description' => "Enjoy the soothing sights and sounds of water at parks with graceful fountains."
            ],
            [
                'name' => "Beautiful Scenery",
                'title' => "Parks with Beautiful Views Near You – Nature at Its Best | Parkscape",
                'description' => "Take in awe-inspiring scenery from parks that offer the most stunning views."
            ],
            [
                'name' => "Breathtaking Views",
                'title' => "Parks with Beautiful Views Near You – Nature at Its Best | Parkscape",
                'description' => "Take in awe-inspiring scenery from parks that offer the most stunning views."
            ],
            [
                'name' => "Botanical Gardens",
                'title' => "Botanical Garden Parks Near You – Floral Beauty Awaits | Parkscape",
                'description' => "Discover the colors, scents, and charm of nearby botanical garden parks."
            ],
            [
                'name' => "Catch the Sunset",
                'title' => "Sunset Viewing Parks Near You – End the Day Beautifully | Parkscape",
                'description' => "Find the perfect park spot to catch a colorful sunset and reflect on the day."
            ],
            [
                'name' => "Statues & Memorials",
                'title' => "Memorial Parks Near You – Honor, History & Art | Parkscape",
                'description' => "Explore parks with iconic statues and memorials that inspire and educate."
            ],
            [
                'name' => "Paintings & Sculptures",
                'title' => "Art Parks Near You – Sculptures & Open-Air Creativity | Parkscape",
                'description' => "Walk through nature and discover artistic installations that bring parks to life."
            ],
            [
                'name' => "Ballin' Basketball Courts",
                'title' => "Basketball Parks Near You – Courts for Casual & Competitive Play | Parkscape",
                'description' => "Hit the court at nearby parks with great basketball setups for fun or fitness."
            ],
            [
                'name' => "Sprints at the Track",
                'title' => "Running Tracks in Parks Near You – Sprint & Stroll Outdoors | Parkscape",
                'description' => "Jog or race on outdoor tracks in local parks. Perfect for training or fresh-air workouts."
            ],
            [
                'name' => "Football with Friends",
                'title' => "Football Fields in Parks Near You – Friendly Matches Await | Parkscape",
                'description' => "Pass, run, and score at parks with football fields ready for team fun."
            ],
            [
                'name' => "Super Soccer Fields",
                'title' => "Soccer Fields Near You – Parks with Goals & Grass | Parkscape",
                'description' => "Find nearby parks with soccer fields for kickabouts and competitive matches."
            ],
            [
                'name' => "Grand Slam Parks",
                'title' => "Baseball Fields in Parks Near You – Swing Into Fun | Parkscape",
                'description' => "Enjoy a day at the park with baseball diamonds perfect for games and practice."
            ],
            [
                'name' => "Great Tennis Courts",
                'title' => "Tennis Parks Near You – Serve, Swing & Rally | Parkscape",
                'description' => "Explore parks with tennis courts for casual hits or competitive sets."
            ],
            [
                'name' => "Pickleball Frenzy",
                'title' => "Pickleball Parks Near You – Smash, Spin & Score | Parkscape",
                'description' => "Play America’s fastest-growing sport at pickleball-friendly parks nearby."
            ],
            [
                'name' => "Untamed Wilderness",
                'title' => "Wilderness Parks Near You – Raw Nature, Real Adventure | Parkscape",
                'description' => "Escape to wild parks with dense forests, rugged trails, and untamed beauty."
            ],
            [
                'name' => "Rugged Coastlines",
                'title' => "Coastal Parks Near You – Trails, Cliffs & Ocean Views | Parkscape",
                'description' => "Breathe in the sea air at parks with striking coastal views and walking paths."
            ],
            [
                'name' => "Remarkable Wildlife",
                'title' => "Wildlife Parks Near You – Spot Nature’s Best | Parkscape",
                'description' => "Get close to nature! Discover parks with diverse animals, birds, and habitats."
            ],
            [
                'name' => "Wondrous Waterfalls",
                'title' => "Waterfall Parks Near You – Splashy Natural Wonders | Parkscape",
                'description' => "Hike to hidden waterfalls and marvel at nature’s power and beauty."
            ],
            [
                'name' => "Aquatic Ecosystems",
                'title' => "Aquatic Parks Near You – Ponds, Lakes & Marshlands | Parkscape",
                'description' => "Discover parks that protect aquatic life and offer calming water views."
            ],
            [
                'name' => "Toboggan Away",
                'title' => "Toboggan Parks Near You – Winter Thrills for Everyone | Parkscape",
                'description' => "Slide into winter fun at parks with hills perfect for tobogganing and sledding."
            ],
            [
                'name' => "X-Country Skiing Enjoyment",
                'title' => "Cross-Country Ski Parks Near You – Glide in the Snow | Parkscape",
                'description' => "Ski through scenic trails and winter forests at local cross-country parks."
            ],
            [
                'name' => "Magical Holiday Lights",
                'title' => "Holiday Light Parks Near You – Festive Nights Outdoors | Parkscape",
                'description' => "Experience parks glowing with holiday cheer, twinkling lights, and family joy."
            ],
            [
                'name' => "Delightful Winter Experiences",
                'title' => "Winter Fun Parks Near You – Cozy, Chilly & Charming | Parkscape",
                'description' => "Discover parks that sparkle with seasonal fun—sledding, cocoa, and snowflakes included."
            ],
            [
                'name' => "Ice Skating Fun",
                'title' => "Ice Skating Parks Near You – Glide into Winter Fun | Parkscape",
                'description' => "Hit the rink under the sky at parks with public skating and frozen joy."
            ],
            [
                'name' => "Wonderful Winter Walks",
                'title' => "Winter Walking Trails Near You – Snowy Serenity Awaits | Parkscape",
                'description' => "Bundle up and stroll through peaceful, snowy paths in nearby parks."
            ],
            [
                'name' => "Thrilling Downhill Skiing",
                'title' => "Downhill Skiing Parks Near You – Snowy Slopes & Speed | Parkscape",
                'description' => "Find parks with ski hills and winter sport facilities for fast-paced fun."
            ],
            [
                'name' => "Gorgeous Fall Foliage",
                'title' => "Fall Foliage Parks Near You – Colors of the Season | Parkscape",
                'description' => "Walk through golden leaves and vibrant trees at the best fall parks."
            ],
            [
                'name' => "Pretty Spooky Strolls",
                'title' => "Halloween Parks Near You – Spooky Trails & Haunted Fun | Parkscape",
                'description' => "Get into the Halloween spirit with eerie park strolls and festive frights."
            ],
            [
                'name' => "Fall Harvest Picking",
                'title' => "Harvest Parks Near You – Pick Apples, Pumpkins & More | Parkscape",
                'description' => "Visit parks with seasonal harvest fun—perfect for families and fall lovers."
            ],
            [
                'name' => "Watercraft Rentals",
                'title' => "Parks with Boat Rentals Near You – Paddle & Explore | Parkscape",
                'description' => "Rent a kayak, canoe, or paddleboat and enjoy the water at your local park."
            ],
            [
                'name' => "Best Beach Days",
                'title' => "Beach Parks Near You – Sun, Sand & Splash Time | Parkscape",
                'description' => "Relax at beachside parks with swimming zones and family-friendly vibes."
            ],
            [
                'name' => "Heated Volleyball",
                'title' => "Volleyball Parks Near You – Sand & Court Action | Parkscape",
                'description' => "Get your game on at parks with volleyball courts and beach setups."
            ],
            [
                'name' => "Cool at the Pool",
                'title' => "Swimming Pool Parks Near You – Dive Into Fun | Parkscape",
                'description' => "Splash into public parks with outdoor pools for fun and fitness."
            ],
            [
                'name' => "Spray Park Splashes",
                'title' => "Spray Parks Near You – Water Fun for Kids | Parkscape",
                'description' => "Beat the heat with splash pads and spray zones at family-friendly parks."
            ],
            [
                'name' => "Historic Battlefields",
                'title' => "Battlefield Parks Near You – Explore U.S. History | Parkscape",
                'description' => "Step back in time at parks where history was made. Educational and scenic."
            ],
            [
                'name' => "The National Mall",
                'title' => "Discover The National Mall – Monuments, Parks & History | Parkscape",
                'description' => "Walk the iconic mall and explore America’s symbols of freedom and memory."
            ],
            [
                'name' => "Incredible American Landscapes",
                'title' => "America’s Best Parks – Iconic Landscapes & Wonders | Parkscape",
                'description' => "Explore parks that define America’s natural beauty—from canyons to coasts on Parkscape."
            ],
            [
                'name' => "The Legendary Rockies",
                'title' => "Explore The Rockies – Majestic Mountain Parks | Parkscape",
                'description' => "Discover national parks in the Rockies for hiking, skiing, and breathtaking views on Parkscape."
            ],
            [
                'name' => "Israel",
                'title' => "Parks in Israel – Discover Nature in Israel | Parkscape",
                'description' => "Explore scenic parks, culture, and natural beauty in Israel. From local hikes to historic views, find your next global adventure."
            ],
            [
                'name' => "Latvia",
                'title' => "Parks in Latvia – Discover Nature in Latvia | Parkscape",
                'description' => "Explore scenic parks, culture, and natural beauty in Latvia. From local hikes to historic views, find your next global adventure."
            ],
            [
                'name' => "Featured Parks",
                'title' => "Explore Featured Parks Near You – Top Picks on Parkscape",
                'description' => "Discover the most loved parks around you. Handpicked for their beauty, fun, and unique charm—see what makes them stand out."
            ],
            [
                'name' => "Kids' Choice Playgrounds",
                'title' => "Best Playgrounds for Kids Near You – Fun & Safe Spots | Parkscape",
                'description' => "Find parks that kids love! Swings, slides, and playful zones perfect for a day of laughter, energy, and outdoor fun."
            ],
            [
                'name' => "BBQs at the Park",
                'title' => "Parks with BBQ Areas Near You – Grill, Chill & Enjoy | Parkscape",
                'description' => "Fire up the grill! Explore parks with BBQ pits, picnic tables, and open-air dining spots for perfect outdoor meals."
            ],
            [
                'name' => "Long Paths & Bike Trails",
                'title' => "Bike-Friendly Parks Near You – Smooth Paths & Scenic Rides | Parkscape",
                'description' => "Pedal through peaceful parks with long bike-friendly paths. Great for relaxed rides or fitness-focused cyclists."
            ],
            [
                'name' => "Hiking by the City",
                'title' => "Urban Hiking Parks Near You – Nature Within Reach | Parkscape",
                'description' => "Step into nature without leaving the city. Explore parks with peaceful walking paths and natural scenery close to home."
            ],
            [
                'name' => "Play the Best",
                'title' => "Top Parks for Sports & Play Near You | Parkscape",
                'description' => "Game on! Find parks with basketball courts, soccer fields, playgrounds, and more—perfect for active days out."
            ],
            [
                'name' => "Outdoor 🏋️‍♀️ Gyms",
                'title' => "Parks with Outdoor Gyms Near You – Work Out in Nature | Parkscape",
                'description' => "Train in the fresh air! Discover parks with fitness equipment, workout stations, and active zones for all levels."
            ],
            [
                'name' => "Summertime Fun",
                'title' => "Parks for Summer Fun Near You – Splash, Relax & Play | Parkscape",
                'description' => "Make the most of sunny days! Explore parks with spray zones, shaded spots, and warm-weather activities for all ages."
            ],
            [
                'name' => "Fine Walks in the Park",
                'title' => "Parks for Leisure Walks Near You – Peaceful & Refreshing | Parkscape",
                'description' => 'Take a gentle stroll through the best walking parks near you. Perfect for relaxation, fresh air, and a little “you” time.'
            ],
            [
                'name' => "Dog Approved Parks",
                'title' => "Dog-Friendly Parks Near You – Wagging Tails Welcome | Parkscape",
                'description' => "Let your pup run free! Discover parks with off-leash areas, walking paths, and dog-approved play zones."
            ],
            [
                'name' => "Famous Burial Sites",
                'title' => "Historic Parks with Famous Burial Sites Near You | Parkscape",
                'description' => "Step into history at parks with memorials and legendary resting places. Discover their stories and scenic grounds."
            ],
            [
                'name' => "World Park Explorer",
                'title' => "Explore Parks from Around the World – Global Green Escapes | Parkscape",
                'description' => "Take a virtual journey through parks across continents. Discover nature’s beauty from Europe to Asia and beyond."
            ],
            [
                'name' => "Monumental Mountains",
                'title' => "Mountain Parks Near You – Majestic Views & Open Skies | Parkscape",
                'description' => "Breathe in the views! Explore parks near famous mountain ranges, perfect for sightseeing, fresh air, and awe-inspiring moments."
            ]
        ];

        try {

            DB::beginTransaction();
            $non = [];
            foreach ($data as $k => $val) {
                $name = $val['name'] ?? null;
                if ($name) {
                    $category = Category::where('name', 'like', "%$name%")->first();
                    if (!$category) {
                        $subcategory = Subcategory::where('name', 'like', "%$name%")->first();
                        if (!$subcategory) {
                            $non[] = $name;
                            dd($non);
                        } else {
                            $subcategory->meta()->updateOrCreate(
                                [],
                                [
                                    'title' => $val['title'],
                                    'description' => $val['description']
                                ]
                            );
                        }
                    } else {
                        $category->meta()->updateOrCreate(
                            [],
                            [
                                'title' => $val['title'],
                                'description' => $val['description']
                            ]
                        );
                    }
                }
            }
            if (!empty($non)) {
                dd($non);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $exc = $e->getMessage() . ' - ' . $e->getLine();
            Log::error($exc);
            dd($exc);
        }
    }

    public function updateSlugsInFeatures()
    {
        $dataName = [
            'Playgrounds',
            'Baseball Fields',
            'Basketball Courts',
            'Football Fields',
            'Pickleball Courts',
            'Skateboard Park',
            'Soccer Fields',
            'Street Hockey Rink',
            'Tennis Courts',
            'Track and Field',
            'Outdoor Gyms',
            'Pull Up Bars',
            'Sit Up Bench',
            'Biking Trails (Bike Paths)',
            'Hiking Trails',
            'Mountain Biking Trails',
            'Nature Walk',
            'Walking Paths',
            'Off Leash Dog Park (Enclosed Space)',
            'Beaches',
            'Boat Launch Ramp',
            'Boat Rentals',
            'Boat Tour',
            'Canoeing (Kayaking)',
            'Fishing',
            'Lake',
            'Splash Pads (Spray Parks)',
            'Swimming Pool (Outdoor)',
            'BBQs/Barbeques (Grill On-Site)',
            'Chess Tables',
            'Community Gardens',
            'Gazebo (Pergola)',
            'Picnic Pavilions',
            'Picnic Tables',
            'Cross Country Skiing',
            'Ice Skating Rink (Outdoor)',
            'Sledding (Tobogganing)',
            'Campground (Campsite)',
            'RV Parking',
            'Arboretum',
            'Botanical Gardens',
            'Memorial (Monument)',
            'Scenic Viewpoint (Lookout)',
            'Sculptures',
            'Statues',
            'Water Fountains',
            'Waterfalls',
            'Wildlife Sanctuary (Nature Preserve)',
            'Bar (Beer Garden)',
            'Concession Stands (Snack Bar)',
            'Performance Stage (Bandshell)',
            'Restaurants',
        ];

        $dataSlug = $this->metaFeatures(false);

        $non = [];

        try {
            DB::beginTransaction();
            foreach ($dataName as $k => $val) {
                $feature = Feature::where('name', 'like', "%$val%")->first();
                if (!$feature) {
                    $featureType = FeatureType::where('name', 'like', "%$val%")->first();
                    if (!$featureType) {
                        $non[$k] = $val;
                    } else {
                        $featureType->update([
                            'slug' => Str::slug($dataSlug[$k]),
                            'name' => $val
                        ]);
                    }
                } else {
                    $feature->update([
                        'slug' => Str::slug($dataSlug[$k]),
                        'name' => $val
                    ]);
                }
            }

            DB::commit();
            if (!empty($non)) {
                dd($non);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            $exc = $ex->getMessage() . ' - ' . $ex->getLine();
            Log::error($exc);
            dd($exc);
        }
    }
}
