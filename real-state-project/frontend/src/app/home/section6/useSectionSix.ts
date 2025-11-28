import { getMarkerLatAndLong } from "@/api/map/map";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { updateIsShowMap } from "@/store/reducer/userReducer";
import { useDebouncedCallback } from "@mantine/hooks";
import { useLoadScript } from "@react-google-maps/api";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useEffect, useRef, useState } from "react";
import libraries from "../libraray";

const useSectionSix = () => {
  const { isShowMap } = useAppSelector((state) => state?.userReducer);

  const dispatch = useAppDispatch();
  const { data, mutate, isPending } = useMutation({
    mutationFn: getMarkerLatAndLong,
    onSuccess: (data) => {
      if (isShowMap !== data?.data?.is_show_map) {
        dispatch(updateIsShowMap(data?.data?.is_show_map));
      }
    },
  });
  const key = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY ?? "";

  const { isLoaded } = useLoadScript({
    googleMapsApiKey: key, // Store API key in environment variables
    libraries: libraries,
    id: "__googleMapsScriptId",
  });

  const [bounds, setBounds] = useState<any>(null);
  const mapRef = useRef<any>(null);
  const [markers, setMarkers] = useState<Array<any>>([]);
  const [selected, setSelected] = useState<any>(null);
  const [id, setId] = useState<null | number>(null);
  const onLoad = (mapInstance: any) => {
    mapRef.current = mapInstance;
  };

  useEffect(() => {
    mutate({
      distance: "50",
      latitude: String(bounds?.lat ?? ""),
      longitude: String(bounds?.lng ?? ""),
    });
  }, [bounds]);
  const { data: markerQuery, isLoading } = useQuery<markerDataType, Error>({
    queryKey: ["mapMarkerData", bounds],
    queryFn: () =>
      getMarkerLatAndLong({
        latitude: String(bounds?.lat ?? ""),
        distance: "50",
        longitude: String(bounds?.lng ?? ""),
      }),
  });

  useEffect(() => {
    if (markerQuery?.data?.lat_lng) {
      setMarkers((prevMarkers) => {
        const markerMap = new Map();
        [...prevMarkers, ...markerQuery?.data?.lat_lng].forEach((marker) => {
          markerMap.set(marker.id, marker); // Assuming each marker has a unique 'id'
        });

        return Array.from(markerMap.values()); // Convert the Map back to an array
      });
    }
  }, [markerQuery]);

  const debouncedHandleBoundsChanged = useDebouncedCallback(() => {
    const map = mapRef.current;

    if (map) {
      const newCenter = map.getCenter();

      newCenter && setBounds({ lat: newCenter?.lat(), lng: newCenter?.lng() });
    }
  }, 300);
  return {
    isShowMap: true,
    debouncedHandleBoundsChanged,
    onLoad,
    setId,
    id,
    selected,
    setSelected,
    isLoaded,
    markers,
    isLoading,
    mapRef,
  };
};

export default useSectionSix;
