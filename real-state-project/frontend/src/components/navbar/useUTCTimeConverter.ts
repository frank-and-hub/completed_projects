import { useState, useEffect } from "react";
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";

dayjs.extend(utc);
dayjs.extend(timezone);

interface MessageAlertDataType {
  image: string;
  StartTime: string;
  EndTime: string;
  id: number;
  start_value_time: string;
  end_value_time: string;
  schedule_type: string;
}

// Custom _dayjs function to handle Safari compatibility
const _dayjs = (date: string | Date, format?: string) => {
  if (typeof date === "string") {
    date = date.replace(/-/g, "/"); // Replace hyphens with slashes for Safari
  }
  return dayjs(date, format);
};

// Hook for converting times to UTC
export const useUTCTimeConverter = () => {
  const convertTimes = (data: messageAlertDataType) => {
    const { start_value_time, end_value_time } = data;

    try {
      const date = dayjs().format("YYYY/MM/DD"); // Use slashes for consistency

      // Construct and parse start time with _dayjs
      const startString = `${date} ${start_value_time}`;
      const utcStart = _dayjs(startString, "YYYY/MM/DD hh:mm A").utc();

      if (!utcStart.isValid()) {
        console.error(`Invalid start time: ${startString}`);
        throw new Error(`Invalid start time: ${startString}`);
      }

      // Construct and parse end time with _dayjs
      const endString = `${date} ${end_value_time}`;
      const utcEnd = _dayjs(endString, "YYYY/MM/DD hh:mm A").utc();

      if (!utcEnd.isValid()) {
        console.error(`Invalid end time: ${endString}`);
        throw new Error(`Invalid end time: ${endString}`);
      }

      return {
        start_time: utcStart.format("HH:mm"),
        end_time: utcEnd.format("HH:mm"),
      };
    } catch (err) {
      throw new Error("Invalid time format");
    }
  };

  return { convertTimes };
};
