import { useAppSelector } from "@/store/hooks";
import { Carousel } from "@mantine/carousel";
import { Box } from "@mantine/core";
import { useEffect } from "react";
import time_icon1 from "../../../public/time_icon1.svg";
import time_icon2 from "../../../public/time_icon2.svg";
import time_icon3 from "../../../public/time_icon3.svg";
import time_icon4 from "../../../public/time_icon4.svg";
import time_icon5 from "../../../public/time_icon5.svg";
import time_icon6 from "../../../public/time_icon6.svg";
import time_icon7 from "../../../public/time_icon7.svg";
import time_icon8 from "../../../public/time_icon8.svg";
import time_icon9 from "../../../public/time_icon9.svg";
import CustomButton from "../customButton/CustomButton";
import SelectTime from "./SelectTime";
import useMessageAlert from "./useMessageAlert";
import ModalCloseIcon from "@/app/home/components/modalCloseIcon/ModalCloseIcon";
// import dayjs from "dayjs";
// import utc from "dayjs/plugin/utc";
// import timezone from "dayjs/plugin/timezone";
import { notification } from "@/utils/notification";
import { useUTCTimeConverter } from "./useUTCTimeConverter";
// Extend Day.js with plugins
// dayjs.extend(utc);
// dayjs.extend(timezone);
const SetTimeInner: messageAlertDataType[] = [
  {
    image: time_icon5,
    StartTime: "12 AM",
    EndTime: "4 AM",
    id: 1,
    start_value_time: "12:00 AM",
    end_value_time: "04:00 AM",
    schedule_type: "1",
  },
  {
    image: time_icon3,
    StartTime: "4 AM",
    EndTime: "6 Am",
    id: 2,
    start_value_time: "04:00 AM",
    end_value_time: "06:00 AM",
    schedule_type: "2",
  },
  {
    image: time_icon2,
    StartTime: "6 AM",
    EndTime: "8 AM",
    id: 3,
    start_value_time: "06:00 AM",
    end_value_time: "08:00 AM",
    schedule_type: "3",
  },
  {
    image: time_icon4,
    StartTime: "8 AM",
    EndTime: "12 PM",
    id: 4,
    start_value_time: "08:00 AM",
    end_value_time: "12:00 PM",
    schedule_type: "4",
  },
  {
    image: time_icon6,
    StartTime: "12 PM",
    EndTime: "2 PM",
    id: 5,
    start_value_time: "12:00 PM",
    end_value_time: "02:00 PM",
    schedule_type: "5",
  },
  {
    image: time_icon1,
    StartTime: "2 PM",
    EndTime: "6 PM",
    id: 6,
    start_value_time: "02:00 PM",
    end_value_time: "06:00 PM",
    schedule_type: "6",
  },
  {
    image: time_icon7,
    StartTime: "6 PM",
    EndTime: "8 PM",
    id: 7,
    start_value_time: "06:00 PM",
    end_value_time: "08:00 PM",
    schedule_type: "7",
  },
  {
    image: time_icon9,
    StartTime: "8 PM",
    EndTime: "10 PM",
    id: 8,
    start_value_time: "08:00 PM",
    end_value_time: "10:00 PM",
    schedule_type: "8",
  },
  {
    image: time_icon8,
    StartTime: "10 PM",
    EndTime: "12 AM",
    id: 9,
    start_value_time: "10:00 PM",
    end_value_time: "12:00 AM",
    schedule_type: "9",
  },
];
const convertTimeToUTC = (timeString: string) => {
  const [hours, minutes] = timeString.split(":").map(Number);
  const localDate = new Date();
  localDate.setHours(hours, minutes, 0, 0);

  const utcHours = localDate.getUTCHours();
  const utcMinutes = localDate.getUTCMinutes();
  const utcSeconds = localDate.getUTCSeconds();

  const utcTimeString = `${String(utcHours).padStart(2, "0")}:${String(
    utcMinutes
  ).padStart(2, "0")}:${String(utcSeconds).padStart(2, "0")}`;

  return utcTimeString;
};
const MessageAlert = ({
  handleClose,
  isClose = true,
}: {
  handleClose?: () => void;
  isClose?: boolean;
}) => {
  const { convertTimes } = useUTCTimeConverter();

  const { isPending, mutate, selectItem, setSelectItem } = useMessageAlert({
    handleClose,
  });
  const { userDetail } = useAppSelector((state) => state?.userReducer);
  useEffect(() => {
    if (userDetail?.schedule_type) {
      setSelectItem(() => {
        const temp = SetTimeInner.find(
          (item) => item?.schedule_type === userDetail?.schedule_type
        );
        return temp ?? null;
      });
    }
  }, [userDetail?.schedule_type]);

  return (
    <div className="set_timer_contain">
      <div className="modal_head_close">
        <h2>Let us know when you should be notified on Whatsapp ?</h2>
        <ModalCloseIcon
          handleClose={() => {
            if (isClose) {
              handleClose && handleClose();
            }
          }}
        />
      </div>

      <Carousel
        className="set_timer_slider"
        height={220}
        slideSize={{ base: "50%", sm: "25%", md: "16.66%" }}
        slideGap={{ base: 1, sm: "md" }}
        align="start"
      >
        {SetTimeInner.map((item) => (
          <Carousel.Slide
            onClick={() => {
              setSelectItem(item);
            }}
          >
            <SelectTime item={item} selectItem={selectItem} />
          </Carousel.Slide>
        ))}
      </Carousel>

      <Box className="button_save">
        <CustomButton
          loading={isPending}
          onClick={() => {
            if (selectItem) {
              // const startTime_utc = convertTimeToUTC(
              //   selectItem?.start_value_time
              // );
              // const endTime_utc = convertTimeToUTC(selectItem?.end_value_time);
              try {
                const utcValue = convertTimes(selectItem);

                mutate({
                  start_time: utcValue?.start_time,
                  end_time: utcValue?.end_time,
                  schedule_type: selectItem?.schedule_type,
                });
              } catch (error) {
                notification({
                  type: "error",
                  message: "Invalid time format",
                });
              }
            }
          }}
        >
          Save
        </CustomButton>
      </Box>
    </div>
  );
};

export default MessageAlert;
// const dateConvertUTC = ({
//   start_value_time,
//   end_value_time,
// }: messageAlertDataType) => {
//   console.log({ start_value_time, end_value_time });

//   try {
//     const date = dayjs().format("YYYY-MM-DD"); // Fixed date for March 20, 2025

//     // Construct and parse start time
//     const startString = `${date} ${start_value_time}`;
//     console.log("Start string:", startString); // Debug: See the exact input
//     const utcStart = dayjs.utc(startString, "YYYY-MM-DD hh:MM A"); // Use "hA" instead of "hhA"
//     console.log(utcStart.format("YYYY-MM-DD HH:mm:ss")); // Debug: See the parsed UTC time

//     if (!utcStart.isValid()) {
//       console.error(`Invalid start time: ${startString}`);
//       return { start_time: "Invalid Date", end_time: "Invalid Date" };
//     }

//     // Construct and parse end time
//     const endString = `${date} ${end_value_time}`;
//     console.log("End string:", endString); // Debug: See the exact input
//     const utcEnd = dayjs.utc(endString, "YYYY-MM-DD hh:MM A");
//     if (!utcEnd.isValid()) {
//       console.error(`Invalid end time: ${endString}`);
//       return { start_time: "Invalid Date", end_time: "Invalid Date" };
//     }

//     return {
//       start_time: utcStart.format("HH:mm"), // e.g., "12:00" if UTC-4
//       end_time: utcEnd.format("HH:mm"), // e.g., "14:00" if UTC-4
//     };
//   } catch (error) {
//     throw new Error("Invalid time format");
//   }
// };
