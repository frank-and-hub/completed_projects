import { getPropertySearchData } from "@/api/propertySearchHistory/propertySearch";
import { searchFilter } from "@/api/search/searchFilter";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import __DEV__ from "@/utils/devCheck";
import { notification } from "@/utils/notification";
import { propertyNeedsQueryKey } from "@/utils/queryKeys/planAmountQueryKey";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { FilterValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useEffect, useMemo, useState } from "react";
type searchDataType = Array<{
  title: string;
  content: Array<{ title: string; content: Array<Array<string>> }>;
}>;
interface initialValueType extends IObject {
  country_name: string;
  province_name: string;
  city: string;
  suburb_name: string;
  property_type: string | null;
  start_price: string;
  end_price: string;
  no_of_bedroom: string;
  no_of_bathroom: string;
  move_in_date: Date | null;
  currency: string | null;
}

export const GOOGLE_PACES_API_BASE_URL =
  "https://maps.googleapis.com/maps/api/place";
const useAdvanceFilter = ({
  handleClose,
  isFromSearch,
}: {
  handleClose?: () => void;
  isFromSearch?: boolean;
}) => {
  const dispatch = useAppDispatch();
  const [searchValue, setSearchValue] = useState<{ [key: string]: string }>({});
  const [checkItem, setCheckItem] = useState<string[]>([]);
  const {
    contextValue: {
      propertySearchData: {
        suburb_name,
        province_name: provinceName,
        country_name: countryName,
        move_in_date,
        property_type,
        no_of_bathroom,
        no_of_bedroom,
        start_price,
        end_price,
        city: cityName,
      },
      cityId,
      country_Id,
      province_Id,
      suburbId,
      currency,
      advanceFeatureData,
      advanceFeatureSelectedData,
      requestAgainData,
      locationData,
    },
    setContextValue,
    setIsModalOpen,
  } = useGlobalContext();
  const data = useGlobalContext();
  console.log(data);

  const { token, userDetail } = useAppSelector((state) => state.userReducer);

  const [advanceSearchData, setAdvanceSearchData] = useState<searchDataType>(
    []
  );

  const [opened, setOpened] = useState(false);
  const [id, setId] = useState<number | null>(null);
  const [idData, setIdData] = useState<{
    countryId: string;
    provinceId: string;
    currency: string;
    cityId: string;
    suburbId: string;
  }>({ countryId: "", provinceId: "", currency: "", cityId: "", suburbId: "" });
  const [currencyData, setCurrencyData] = useState<
    Array<{ value: string; label: string }>
  >([]);
  const [isOpenedByHover, setIsOpenedByHover] = useState<boolean>(false);
  const [searchData, setSearchData] = useState<searchDataType>([]);

  const [selectedData, setSelectedData] = useState<{
    [key: string]: Array<{ title: string; value: string }>;
  } | null>(null);
  const [advanceSelectedData, setAdvanceSelectedData] = useState<
    Array<{ title: string; value: Array<string>; id: number }>
  >([]);
  const queryClient = useQueryClient();
  const { data: propertyData, isLoading: propertyLoading } = useQuery({
    queryKey: ["propertySearchData"],
    queryFn: getPropertySearchData,
  });

  const onMouseEnter = (index: number) => {
    setOpened(true);
    id === index ? setId(null) : setId(index);
    setIsOpenedByHover(true);
  };
  const onCloseDropDown = (index: number) => {
    setId(null);
    setOpened(false);
    setSearchValue((prev) => ({ ...prev, [String(index)]: "" }));
    setAdvanceSearchData(searchData);
    setIsOpenedByHover(false);
  };

  useEffect(() => {
    if (propertyData?.data) {
      const temp: Array<any> = [];
      propertyData?.data?.map((item: any) => {
        const obj: { [key: string]: any } = {};
        for (let key in item) {
          const arr = [];
          for (let i in item[key]) {
            arr.push({ title: i, content: item?.[key]?.[i] });
          }

          temp.push({ title: key, content: arr });
        }
      });

      setAdvanceSearchData(temp);
      setSearchData(temp);
    }
  }, [propertyData]);

  const form = useForm<initialValueType>({
    initialValues: {
      suburb_name: isFromSearch && suburb_name ? suburb_name : "",
      property_type: isFromSearch && property_type ? property_type : null,
      no_of_bathroom: __DEV__
        ? "1"
        : isFromSearch && no_of_bathroom
        ? no_of_bathroom
        : "",
      no_of_bedroom: __DEV__
        ? "1"
        : isFromSearch && no_of_bedroom
        ? no_of_bedroom
        : "",
      move_in_date: isFromSearch && move_in_date ? move_in_date : null,
      currency: isFromSearch && currency ? currency : null,
      city: isFromSearch && cityName ? cityName : "",
      country_name: isFromSearch && countryName ? countryName : "",
      end_price: __DEV__ ? "100" : isFromSearch && end_price ? end_price : "",
      province_name: isFromSearch && provinceName ? provinceName : "",
      start_price: __DEV__
        ? "1"
        : isFromSearch && start_price
        ? start_price
        : "",
    },
    validate: yupResolver(FilterValidationSchema),
    validateInputOnBlur: true,
    validateInputOnChange: true,
  });

  const { country_name, city, province_name } = form.values;

  const { mutate, isPending } = useMutation({
    mutationFn: searchFilter,
    onSuccess: async (data) => {
      try {
        if (data?.data?.total_request === 5) {
        }
        handleClose && handleClose();

        await queryClient.invalidateQueries({
          queryKey: [...profileQueryKey.list],
        });
        const currentDate = new Date();
        const fifteenDaysAgo = new Date(currentDate);
        fifteenDaysAgo.setDate(currentDate.getDate() - 10);
        await queryClient.invalidateQueries({
          queryKey: [
            ...propertyNeedsQueryKey.list,
            fifteenDaysAgo,
            currentDate,
          ],
        });
        notification({
          message: "Your request has been submitted.",
        });
        setIsModalOpen("thankYou");
      } catch (err) {
        console.error(err);
      }
    },
  });

  useEffect(() => {
    if (idData?.currency) {
      setCurrencyData([
        {
          label: idData?.currency,
          value: idData?.currency,
        },
      ]);
      form.setFieldValue("currency", idData?.currency);
    }
  }, [idData?.currency]);
  useEffect(() => {
    if (isFromSearch && currency) {
      setCurrencyData([
        {
          label: currency,
          value: currency,
        },
      ]);
      form.setFieldValue("currency", currency);
    }
  }, [isFromSearch]);
  useEffect(() => {
    if (requestAgainData && advanceSearchData) {
      const temp: any = {};
      advanceSearchData?.map((item, index) => {
        if (requestAgainData[item?.title]) {
          for (let key in requestAgainData[item?.title]) {
            requestAgainData[item?.title][key]?.map((item) => {
              if (temp?.[index]) {
                temp?.[index]?.push({
                  title: key,
                  value: item,
                });
              } else {
                temp[index] = [{ title: key, value: item }];
              }
            });
          }
          setSelectedData(temp);
        }
      });
    }
  }, [requestAgainData, advanceSearchData]);
  // useEffect(() => {
  //   form.setValues((prev) => ({
  //     ...prev,
  //     city: '',
  //     province_name: '',
  //     suburb_name: '',
  //   }));
  //   setIdData((prev) => ({
  //     ...prev,
  //     cityId: '',
  //     provinceId: '',
  //     suburbId: '',
  //   }));
  // }, [country_name]);

  // useEffect(() => {
  //   form.setValues((prev) => ({
  //     ...prev,
  //     city: '',
  //     suburb_name: '',
  //   }));
  //   setIdData((prev) => ({
  //     ...prev,
  //     cityId: '',
  //     suburbId: '',
  //   }));
  // }, [province_name]);
  // useEffect(() => {
  //   form.setValues((prev) => ({
  //     ...prev,
  //     suburb_name: '',
  //   }));
  //   setIdData((prev) => ({
  //     ...prev,
  //     suburbId: '',
  //   }));
  // }, [city]);
  const onSelectChangeData = (value: string, ind: number) => {
    setAdvanceSearchData((prev) => {
      const tempData = [...searchData];
      const data = tempData?.map((item, index) => {
        if (index !== ind) {
          return item;
        } else {
          const obj: { child?: number } = {};
          let isFind: boolean = false;
          const temp = item?.content?.map((i, index) => {
            const arr = i.content?.filter((iir) => {
              const t = iir?.[0]?.replace(/([a-z])([A-Z])/g, "$1 $2");
              return t.toLowerCase().includes(value.toLowerCase());
            });
            if (arr.length && value) {
              setOpened(true);
              setId(ind);
              isFind = true;
            }
            if (
              !isFind &&
              index === item?.content?.length - 1 &&
              !isOpenedByHover
            ) {
              setOpened(false);
              setId(null);
            }
            if (!value && !isOpenedByHover) {
              setOpened(false);
              setId(null);
            }

            if (arr?.length && value) {
              obj.child = index;
              return { ...i, content: arr };
            } else {
              return i;
            }
          });

          if (obj?.child || obj?.child === 0) {
            const filteredData = temp?.filter(
              (item, index) => index === obj?.child
            );
            return { ...item, content: filteredData };
          } else {
            return { ...item, content: temp };
          }
        }
      });

      return data;
    });
    setSearchValue((prev) => ({ ...prev, [String(ind)]: value }));
  };

  const defaultCountryData = useMemo(() => {
    if (isFromSearch && countryName && country_Id) {
      return {
        label: countryName ?? "",
        value: Number(country_Id),
        id: Number(country_Id),
      };
    }

    return null;
  }, [isFromSearch]);
  const defaultProvinceData = useMemo(() => {
    if (isFromSearch && provinceName && province_Id) {
      return {
        label: provinceName ?? "",
        value: province_Id,
        id: province_Id,
      };
    }

    return null;
  }, [isFromSearch, provinceName, province_Id]);

  const defaultCityData = useMemo(() => {
    if (isFromSearch && cityName && cityId) {
      return {
        label: cityName ?? "",
        value: cityId,
        id: cityId,
      };
    }

    return null;
  }, [isFromSearch, cityName, cityId]);
  const defaultSuburbData = useMemo(() => {
    if (isFromSearch && suburb_name && suburbId) {
      return {
        label: suburb_name ?? "",
        value: suburbId,
        id: suburbId,
      };
    }

    return null;
  }, [isFromSearch, suburb_name, suburbId]);
  useEffect(() => {
    if (isFromSearch && advanceFeatureData) {
      setSelectedData(advanceFeatureData);
    }
  }, [isFromSearch]);
  useEffect(() => {
    if (isFromSearch && advanceFeatureSelectedData) {
      setAdvanceSelectedData(advanceFeatureSelectedData);
    }
  }, []);

  const onSelectHandler = (value: {
    index: number;
    name: string;
    title: string;
    heading: string;
  }) => {
    let temp = [...checkItem];
    temp.push(value?.name);
    const isSelected = selectedData?.[String(value?.index)]?.find(
      (item) => item?.value === value?.name
    );

    setCheckItem(temp);
    setAdvanceSelectedData((prev) => {
      const arr = [...prev];
      if (isSelected) {
        return prev;
      } else {
        const indexPre = arr?.findIndex((i) => i?.id === value?.index);
        if (indexPre !== -1) {
          arr.splice(indexPre, 1, {
            ...arr?.[indexPre],
            value: [...arr?.[indexPre]?.value, value?.name],
          });
        } else {
          arr.push({
            id: value?.index,
            title: value?.heading,
            value: [value?.name],
          });
        }

        return arr;
      }
    });
    setSelectedData((prev) => {
      const temp = { ...prev };
      const isSelected = temp?.[String(value?.index)]?.find(
        (item) => item?.value === value?.name
      );

      if (isSelected) {
        return prev;
      } else {
        if (temp?.[String(value?.index)]) {
          temp?.[String(value?.index)]?.push({
            title: value?.title,
            value: value?.name,
          });
          return temp;
        } else {
          temp[String(value?.index)] = [
            { title: value?.title, value: value?.name },
          ];
          return temp;
        }
      }
    });
    setSearchValue((prev) => ({ ...prev, [String(value?.index)]: "" }));
  };

  const onRemoveHandler = ({
    index,
    value,
  }: {
    value: string;
    index: number;
  }) => {
    let temp = [...checkItem];
    let findIndex = temp?.findIndex((ele) => ele === value);
    temp.splice(findIndex, 1);
    setCheckItem(temp);
    setSelectedData((prev) => {
      const temp = { ...prev };

      const arr = temp?.[String(index)]?.filter(
        (item) => item?.value.toLowerCase() !== value.toLowerCase()
      );
      const obj = { ...prev, [String(index)]: arr };
      return obj;
    });
    setAdvanceSelectedData((prev) => {
      const arr = [...prev];
      const indexPre = arr?.findIndex((i) => i?.id === index);
      const filteredData = arr?.[indexPre]?.value?.filter(
        (item) => item?.toLocaleLowerCase() !== value?.toLocaleLowerCase()
      );
      if (filteredData?.length) {
        arr.splice(indexPre, 1, { ...arr?.[indexPre], value: filteredData });
      } else {
        arr.splice(indexPre, 1);
      }
      return arr;
    });
  };

  const handleSubmit = form.onSubmit((data) => {
    const obj: any = {};

    if (selectedData) {
      for (let key in selectedData) {
        selectedData[key]?.map((item) => {
          obj[item?.title] = { ...obj[item?.title], [item?.value]: "1" };
        });
      }
    }
    if (!userDetail?.subscription) {
      dispatch(updatePropertyInformation({ ...data, ...obj }));
      dispatch(updatePropertySearch(true));
    }
    if (token && Boolean(userDetail?.subscription)) {
      window.dispatchEvent(new Event("new-event"));
      const payload: searchFilterParamsType = { ...obj, ...data };

      mutate(payload);
    } else if (token && !userDetail?.subscription) {
      setIsModalOpen("selectPlan");
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        isSearchApiCall: true,
        propertySearchData: {
          ...data,
          ...obj,
        },
      }));
      handleClose && handleClose();
    } else if (!token) {
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        isSearchApiCall: true,
        propertySearchData: {
          ...data,
          ...obj,
          currency: data?.currency ?? "",
        },
        cityId: idData?.cityId,
        country_Id: Number(idData?.countryId),
        province_Id: idData?.provinceId,
        suburbId: idData?.suburbId,
        currency: data?.currency ?? "",
        advanceFeatureData: selectedData,
        advanceFeatureSelectedData: advanceSelectedData,
      }));

      handleClose && handleClose();
      setTimeout(
        () => {
          setIsModalOpen("login");
        },
        isFromSearch ? 0 : 500
      );
    }
  });

  const resetFilter = () => {
    setSelectedData({});
    setCurrencyData([]);
    setIdData({
      cityId: "",
      countryId: "",
      currency: "",
      provinceId: "",
      suburbId: "",
    });
    setContextValue((prev: contextValuesType) => ({
      ...prev,
      advanceFeatureData: {},
      propertySearchData: {},
      country_Id: 0,
      suburbId: "",
      cityId: "",
      province_Id: "",
      currency: "",
      advanceFeatureSelectedData: [],
    }));

    form.reset();
    form.setValues((prev) => ({
      city: "",
      property_type: null,
      suburb_name: "",
      province_name: "",

      no_of_bathroom: "",
      no_of_bedroom: "",
      move_in_date: null,
      country_name: "",
      currency: null,
      start_price: "",
      end_price: "",
    }));
  };

  return {
    form,
    resetFilter,
    handleSubmit,
    isPending,
    userDetail,
    setContextValue,
    onSelectChangeData,
    advanceSearchData,
    onSelectHandler,
    propertyLoading,
    selectedData,
    onRemoveHandler,
    currencyData,
    searchValue,
    onMouseEnter,
    onCloseDropDown,
    opened,
    id,
    setIdData,
    idData,
    defaultCountryData,
    defaultCityData,
    defaultSuburbData,
    defaultProvinceData,
    checkItem,
  };
};

export default useAdvanceFilter;
