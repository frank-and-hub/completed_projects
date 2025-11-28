import { getDummyPropertyById } from "@/api/dummyProperties/dummyProperties";
import {
  propertyDetail,
  propertyDetailMap,
  propertyEnquiry,
} from "@/api/propertySearchHistory/propertySearch";
import { useAppSelector } from "@/store/hooks";
import { notification } from "@/utils/notification";
import {
  propertyDetailMapQueryKey,
  propertyDetailQueryKey,
} from "@/utils/queryKeys/transactionHistoryKeys";
import { RequestEnquiryValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useSearchParams } from "next/navigation";
import { useEffect, useMemo, useState } from "react";
type advanceFeatureDataShowType = Array<{
  title: string;
  content: Array<{ title: string; content: Array<string> }>;
}>;
const usePropertyDetail = () => {
  const [isNewModalOpen, setIsNewModalOpen] = useState<string>("");
  const [advanceFeatureData, setAdvanceFeatureData] =
    useState<advanceFeatureDataShowType>([]);
  const searchParams = useSearchParams();
  const search = searchParams?.get("property_id");
  const isFromMap = searchParams?.get("updateKey");

  const {
    data,
    isLoading,
    isPending: externalPending,
  } = useQuery<propertyDetailType, Error>({
    queryKey: [...propertyDetailQueryKey.list, search],
    queryFn: () => propertyDetail(search!),
    enabled: !!search && isFromMap === "external",
  });

  const {
    data: mapPropertyDetail,
    isLoading: mapLoading,
    isPending: internalPending,
  } = useQuery<mapPropertyDetailsType, Error>({
    queryKey: [...propertyDetailMapQueryKey.list, search],
    queryFn: () => propertyDetailMap({ id: search! }),
    enabled: !!search && isFromMap === "internal",
  });
  const {
    data: dummyPropertyDetail,
    isLoading: dummyLoading,
    isPending: dummyPending,
  } = useQuery<mapPropertyDetailsType, Error>({
    queryKey: [...propertyDetailMapQueryKey.list, search],
    queryFn: () =>
      getDummyPropertyById({
        propertyId: search as string,
      }),
    enabled: !!search && isFromMap === "dummy",
  });
  useEffect(() => {
    if (mapPropertyDetail?.data?.advanced_feature) {
      const temp: Array<any> = [];
      // const obj: { [key: string]: any } = {};
      const item: any = mapPropertyDetail?.data?.advanced_feature;
      for (let key in item) {
        const arr = [];
        for (let i in item[key]) {
          arr.push({ title: i, content: item?.[key]?.[i] });
        }
        if (arr.length) {
          temp.push({ title: key, content: arr });
        }
      }
      setAdvanceFeatureData(temp);
    }
  }, [mapPropertyDetail]);

  const { userDetail } = useAppSelector((state) => state?.userReducer);
  const form = useForm<{
    name: string;
    email: string;
    phone: string;
    message: string;
  }>({
    initialValues: {
      email: userDetail?.email!,
      message: "",
      name: userDetail?.name!,
      phone: userDetail?.phone!,
    },
    validate: yupResolver(RequestEnquiryValidationSchema),
  });
  const { mutate, isPending: enquiryLoading } = useMutation({
    mutationFn: propertyEnquiry,
    onSuccess: () => {
      // form.setFieldValue('email', '');
      form.setFieldValue("message", "");
      form.setFieldValue("name", "");
      // form.setFieldValue('phone', '');
      notification({
        message: "Your enquiry has been submitted.",
      });
    },
  });
  const enquirySubmitHandler = form.onSubmit((values) => {
    const { email, message, name, phone } = values;

    mutate({
      email,
      full_name: name,
      message,
      phone,
      property_id: search!,
    });
  });

  const propertyData = useMemo(
    () => (isFromMap === "dummy" ? dummyPropertyDetail : mapPropertyDetail),
    [dummyPropertyDetail, mapPropertyDetail]
  );
  return {
    data,
    isPending: isLoading,
    form,
    enquirySubmitHandler,
    enquiryLoading,
    isNewModalOpen,
    setIsNewModalOpen,
    isFromMap,
    mapLoading,
    mapPropertyDetail: propertyData,
    advanceFeatureData,
    externalPending,
    internalPending,
    dummyLoading,
    dummyPropertyDetail,
  };
};

export default usePropertyDetail;
