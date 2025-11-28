import dayjs from "dayjs";

/**
 * Function to check if a given date and time have passed compared to the current date and time.
 * @param dateStr - The date string in the format "DDth MMMM YYYY" (e.g., "14th February 2025")
 * @param timeStr - The time string in the format "HH:MM AM/PM" (e.g., "11:00 AM")
 * @returns boolean - True if the date and time have passed, false otherwise
 * @throws Error - If the date or time format is invalid
 */
export default function hasDateTimePassed(
  dateStr: string,
  timeStr: string
): boolean {
  try {
    // Step 1: Parse the date string (e.g., "14th February 2025")
    const cleanedDateStr = dateStr?.replace(/(st|nd|rd|th|o)/, ""); // Remove suffixes like "th", "st", etc.
    const dateParts = cleanedDateStr ? cleanedDateStr.split(" ") : [];
    if (dateParts.length !== 3) {
      throw new Error('Invalid date format. Expected format: "DDth MMMM YYYY"');
    }

    const day = parseInt(dateParts[0], 10);
    const monthName = dateParts[1];
    const year = parseInt(dateParts[2], 10);
    
    // Map month name to month index (0-11)
    const monthNames = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ];
    const monthIndex = monthNames.findIndex(
      (m) => m.toLowerCase() === monthName.toLowerCase()
    );
    
    if (monthIndex === -1) {
      throw new Error("Invalid month name");
    }

    // Step 2: Parse the time string (e.g., "11:00 AM")
    const timeParts = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
    if (!timeParts) {
      throw new Error('Invalid time format. Expected format: "HH:MM AM/PM"');
    }

    let hours = parseInt(timeParts[1], 10);
    const minutes = parseInt(timeParts[2], 10);
    const period = timeParts[3].toUpperCase();

    // Convert to 24-hour format
    if (period === "PM" && hours !== 12) {
      hours += 12;
    } else if (period === "AM" && hours === 12) {
      hours = 0;
    }

    // Step 3: Create a Date object for the provided date and time
    const inputDate = new Date(year, monthIndex, day, hours, minutes);

    // Step 4: Get the current date and time
    const currentDate = new Date();
console.log(inputDate.getTime() , currentDate?.getTime());
    // Step 5: Compare the two dates
    return inputDate.getTime() <= currentDate?.getTime();
  } catch (error) {
    throw new Error(
      `Failed to parse date or time: ${(error as Error).message}`
    );
  }
}

const showDate = (dateString: string) => {
  // Format the date with custom suffix
  // const formattedDate = dayjs(utcTime).format('D MMMM YYYY');

  // Function to add ordinal suffix (st, nd, rd, th)
  // const getOrdinalDate = (dateString) => {
  const day = dayjs(dateString).date();
  const suffix =
    day % 10 === 1 && day !== 11
      ? "st"
      : day % 10 === 2 && day !== 12
        ? "nd"
        : day % 10 === 3 && day !== 13
          ? "rd"
          : "th";
  return `${day}${suffix} ${dayjs(dateString).format("MMMM YYYY")}`;
  // };
};

const monthMap: any = {
  January: "Jan",
  February: "Feb",
  March: "Mar",
  April: "Apr",
  May: "May",
  June: "Jun",
  July: "Jul",
  August: "Aug",
  September: "Sep",
  October: "Oct",
  November: "Nov",
  December: "Dec",
};

export { monthMap, showDate };
