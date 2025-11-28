import CustomText from "@/components/customText/CustomText";
import React, { useEffect, useState } from "react";
type TCountdownTimer = {
  initialValue?: number;
  onCountDownEnd?: () => void;
};
const OtpTimer = ({ initialValue = 300, onCountDownEnd }: TCountdownTimer) => {
  const [timerCount, setTimer] = useState(initialValue);
  const [minutes, setMinutes] = useState(initialValue / 60 - 1);

  useEffect(() => {
    let interval = setInterval(() => {
      setTimer((lastTimerCount) => {
        setMinutes((prev) => {
          lastTimerCount <= 1 && clearInterval(interval);
          if (lastTimerCount - prev * 60 === 1) {
            return prev - 1;
          } else {
            return prev;
          }
        });
        return lastTimerCount - 1;
      });
    }, 1000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (timerCount === 1) {
      onCountDownEnd && onCountDownEnd();
    }
  }, [timerCount]);

  return (
    <CustomText style={{ color: "gray" }}>
      {`Resend OTP in ${minutes < 1 ? "" : minutes + ":"}${
        timerCount - minutes * 60
      }`}
    </CustomText>
  );
};

export default OtpTimer;
