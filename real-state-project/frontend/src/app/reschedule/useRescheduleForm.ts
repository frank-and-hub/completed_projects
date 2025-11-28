import { formatTimeWithSeconds } from "@/utils/capitalizeFiesrtLetter";
import { useForm, yupResolver } from "@mantine/form";
import { getDay, isWithinInterval } from "date-fns";
import * as yup from "yup";

interface timeSlot {
  days_in_week: string[] | string; // Can be array or stringified array
  end_time: string;
  start_time: string;
}

interface eventSlot {
  id: string;
  date: string | number | Date;
  description: string | null;
  link: string | null;
  time: string | number | Date;
  title: string;
  status: string;
  admin?: {
    name: string;
    image: string | null;
  }
  property?: {
    id: string;
    name: string;
    country: string | null;
    province: string | null;
    suburb: string | null;
    town: string | null;
    address: string;
    complete_address: string;
  },
  time_limit?: string | null;
  time_slot?: timeSlot;
}

const useRescheduleForm = (
  timeSlot: timeSlot,
  event: eventSlot,
  propertyId: string | null
) => {
  const weekdayMap: Record<string, number> = {
    Sunday: 0,
    Monday: 1,
    Tuesday: 2,
    Wednesday: 3,
    Thursday: 4,
    Friday: 5,
    Saturday: 6,
  };

  // const parseTime = (time: string) => parse(time, "HH:mm", new Date());
  const parseTime = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(":").map(Number);
    const date = new Date();
    date.setUTCHours(hours);
    date.setUTCMinutes(minutes);
    date.setUTCSeconds(0);
    date.setUTCMilliseconds(0);
    return date;
  }

  // Safely parse days_in_week (in case it's a string)
  let parsedDaysInWeek: string[] = [];
  try {
    parsedDaysInWeek = Array.isArray(timeSlot.days_in_week)
      ? timeSlot.days_in_week
      : JSON.parse(timeSlot.days_in_week || "[]");
  } catch (error) {
    console.error("Invalid days_in_week format:", timeSlot.days_in_week);
  }

  const getRescheduleSchema = ({
    allowedWeekdays = ["Monday"],
    timeStart = "00:00",
    timeEnd = "23:00",
    eventDetails = event,
  }: {
    allowedWeekdays?: string[];
    timeStart?: string;
    timeEnd?: string;
    eventDetails?: eventSlot;
  }) => {

    const dateField = yup
      .date()
      .typeError("Invalid date format")
      .required("Date is required")
      .test("min-date-from-event", function (value) {
        if (!value) return false;
        console.info(eventDetails);
        const eventDate = eventDetails?.date ? new Date(eventDetails.date) : null;
        const eventLocaleDateString = eventDate?.toLocaleDateString();
        const newValue = new Date(value);
        if (eventDate && newValue < eventDate) {
          return this.createError({
            message: `Date must be on or after ${eventLocaleDateString}`,
          });
        }
        return true;
      })
      .test("max-date-if-timeslot", function (value) {
        if (!value) return false;
        if (eventDetails?.time_limit) {
          const maxDate = new Date(eventDetails.time_limit);
          if (value > maxDate) {
            return this.createError({
              message: `Date must be on or before ${maxDate.toLocaleDateString()}`,
            });
          }
        }
        return true;
      })
      .test(
        "is-valid-day",
        `Date must be one of: ${allowedWeekdays.join(", ")}`,
        (value) => {
          if (!value) return false;
          const selectedDay = getDay(value);
          const allowedDays = allowedWeekdays.map((day) => weekdayMap[day]);
          return allowedDays.includes(selectedDay);
        }
      );

    return yup.object().shape({
      date: dateField,
      time: yup
        .string()
        .required("Time is required")
        .test(
          "is-valid-time",
          `Time must be between ${timeStart} and ${timeEnd}`,
          (value) => {
            if (!value) return false;
            try {
              const input = parseTime(value);
              return isWithinInterval(input, {
                start: parseTime(timeStart),
                end: parseTime(timeEnd),
              });
            } catch (e) {
              return false;
            }
          }
        ),
      message: yup
        .string()
        .nullable()
        .notRequired()
        .transform((val) => (val === "" ? null : val)),
    });
  };

  const form = useForm({
    initialValues: {
      date: "",
      time: "",
      message: "",
      property_id: propertyId,
    },
    validate: yupResolver(
      getRescheduleSchema({
        allowedWeekdays: parsedDaysInWeek,
        timeStart: formatTimeWithSeconds(timeSlot.start_time),
        timeEnd: formatTimeWithSeconds(timeSlot.end_time),
        eventDetails: event,
      })
    ),
  });

  return {
    form,
  };
};

export default useRescheduleForm;
