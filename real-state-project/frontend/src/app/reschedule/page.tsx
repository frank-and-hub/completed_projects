"use client";
import {
    ActionIcon,
    Box,
    Center,
    Loader,
    Paper,
    Text,
    Textarea,
    TextInput,
    Title,
} from "@mantine/core";
import { useQuery } from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import React, { useEffect, useMemo, useRef, useState } from "react";
import "./reschedule.scss";
import CustomButton from "@/components/customButton/CustomButton";
import useRescheduleForm from "./useRescheduleForm";
import { propertyEvevntDetailMap } from "@/api/propertySearchHistory/propertySearch";
import { reshedulePropertyInviteApi } from "@/api/request/request";
import { IconCalendarMonth, IconClock } from "@tabler/icons-react";
import { notification } from "@/utils/notification";
import { TimeInput } from '@mantine/dates';
import { useAppSelector } from "@/store/hooks";

function Page() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const property_id = searchParams.get("property");
    const id = searchParams.get("id");

    const {
        data: eventData,
        isLoading,
    } = useQuery({
        queryKey: ["thank_you", property_id],
        queryFn: () => propertyEvevntDetailMap({ id: id as string }),
        enabled: !!(id && property_id),
    });

    const timeSlot = eventData?.data?.timeSlot ?? {};
    const event = eventData?.data ?? {};
    const token = useAppSelector(state => state?.userReducer?.token) ?? null

    const { form } = useRescheduleForm(timeSlot, event, property_id);
    const [submitting, setSubmitting] = useState(false);

    const formattedTime = useMemo(() => {
        if (event?.date) {
            const localDate = new Date(event.date);

            // Format to local time in HH:MM format
            return localDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false, // Change to true if you want AM/PM
            });
        }
        return '';
    }, [event]);

    useEffect(() => {
        if (event?.date && formattedTime) {
            const parsedDate = new Date(event.date);
            const yyyy = parsedDate.getFullYear();
            const mm = String(parsedDate.getMonth() + 1).padStart(2, '0');
            const dd = String(parsedDate.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;
            form.setValues({
                date: formattedDate,
                time: formattedTime,
                message: '',
            });
        }
    }, [event, formattedTime]);

    const handleSubmit = form.onSubmit(async (data) => {
        setSubmitting(true);
        try {
            const localDateTime = new Date(`${data.date}T${data.time}`); // Creates a local time
            const t = localDateTime.toISOString().slice(11, 16);
            const res = await reshedulePropertyInviteApi(id, {
                ...data,
                time: t,
            });
            if (res.status) {
                notification({
                    message: "Your property requests event has been resheduled.",
                });
            }
        } catch (error) {
            console.error("Submission error:", error);
        } finally {
            if (token) {
                router.push('/calendar-events');
            } else {
                router.push('/');
            }
        }
    });

    const icon = (
        <IconCalendarMonth
            style={{ color: "#F30051", width: 18, height: 18 }}
            stroke={1.5}
        />
    );

    useEffect(() => {
        if (token) {
            if (event && event.status === null && event.status !== 'pending') {
                notification({
                    title: "Error",
                    message: `Property request event not found.`,
                    type: "error",
                });
            } else if (event.status === 're-schedule') {
                router.push('/calendar-events');
                notification({
                    title: "Information",
                    message: `The property request event has already been rescheduled.`,
                    type: "success",
                });
            }
        }
        console.info(event, token);
        // console.clear();
    }, [event, router, token]);

    const ref = useRef<HTMLInputElement>(null);

    const pickerControl = (
        <ActionIcon variant="subtle" color="gray" onClick={() => ref.current?.showPicker()}>
            <IconClock size={16} stroke={1.5} />
        </ActionIcon>
    );

    function formatTime(timeStr: string) {
        const date = new Date(`1970-01-01T${timeStr}`);
        return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }

    const startTime = timeSlot?.start_time ? formatTime(timeSlot.start_time) : '00:00';
    const endTime = timeSlot?.end_time ? formatTime(timeSlot.end_time) : '00:00';
    const weekdays = timeSlot?.days_in_week ? JSON.parse(timeSlot.days_in_week) : [];

    const stringWeekdays = weekdays
        .map((day: string) => day.trim().slice(0, 3)) // First 3 letters
        .map((short: any) => short.charAt(0).toUpperCase() + short.slice(1).toLowerCase()) // Capitalize
        .join('/');

    return (
        <section className="main_section">
            <Paper className="content_box" shadow="md" radius="md">
                {isLoading ? (
                    <Center>
                        <Loader size={36} />
                    </Center>
                ) : (event.id && (
                    <>
                        <Title ta="center" px="xl">Reschedule Event</Title>
                        <Text size="xs" mt="md">
                            Landlord/Agent is only available at following time slot: <br />
                            <strong>Days:</strong> {stringWeekdays} <br />
                            <strong>Time:</strong> {startTime} - {endTime} <br />
                            Please select the meeting time accordingly.
                        </Text>

                        <TextInput
                            label="Date"
                            type="date"
                            mt="lg"
                            variant="filled"
                            withAsterisk={true}
                            {...form.getInputProps("date")}
                        />

                        <TimeInput
                            label="Time"
                            type="time"
                            mt="lg"
                            variant="filled"
                            step="0"
                            withSeconds={false}
                            withAsterisk={true}
                            {...form.getInputProps("time")}
                            ref={ref}
                            rightSection={pickerControl}
                        />

                        <Textarea
                            label="Message"
                            placeholder="Enter message here"
                            mt="lg"
                            autosize
                            minRows={5}
                            variant="filled"
                            {...form.getInputProps("message")}
                        />

                        <Box mt="sm">
                            <CustomButton loading={submitting} onClick={() => handleSubmit()}>
                                Submit
                            </CustomButton>
                        </Box>
                    </>
                )
                )}
            </Paper>
        </section>
    );
}

export default Page;
