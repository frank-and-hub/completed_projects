import image1 from "../../assets/images/house_prt_1.png";
import image2 from "../../assets/images/house_prt_1.png";
export const properties = [
  {
    slug: "luxury-villa-jeddah",
    name: "Luxury 4BHK Villa for Rent in Jeddah",
    description:
      "A luxurious 4-bedroom villa with a private pool, garden, and sea view.",
    image: image1,

    datePosted: "2025-02-19",
    address: {
      street: "Corniche Road",
      city: "KwaZulu",
      country: "SA",
    },
    type: "villa",
    rooms: 4,
    floorSize: 350,
    amenities: ["Private Pool", "Garden", "Garage"],
    price: 15000,
    leaseLength: 12,
  },
  {
    slug: "apartment-riyadh",
    name: "2-Bedroom Apartment for Rent in Riyadh",
    description:
      "A spacious 2-bedroom apartment available for rent with modern amenities.",
    image: image2,
    datePosted: "2025-02-19",
    address: {
      street: "King Fahd Road",
      city: "Guateng",
      country: "SA",
    },
    type: "apartment",
    rooms: 2,
    floorSize: 120,
    amenities: ["Swimming Pool", "Gym", "Covered Parking"],
    price: 4500,
    leaseLength: 12,
  },
];
