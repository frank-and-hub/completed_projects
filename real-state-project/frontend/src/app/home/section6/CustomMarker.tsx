import { capitalizeFirstLetter } from "@/utils/capitalizeFiesrtLetter";
import { Image } from "@mantine/core";
import { InfoWindow } from "@react-google-maps/api";
import { IconX } from "@tabler/icons-react";
import Link from "next/link";

function CustomMarker({ setSelected, setId, selected, timerRef }: any) {
  return (
    <InfoWindow
      options={{
        headerDisabled: true,
        headerContent: "bhjh",
      }}
      onCloseClick={() => {
        setSelected(null);
        setId(null);
      }}
      position={{
        lat: Number(selected.lat),
        lng: Number(selected.lng),
      }}
    >
      <Link
        target="_blank"
        href={`/property-detail?property_id=${selected?.id}&updateKey=${
          selected?.type ?? "internal"
        }`}
      >
        <div className="detail_card">
          <IconX
            style={{
              position: "absolute",
              right: 8,
              top: 5,
              cursor: "pointer",
            }}
            onClick={(event) => {
              event?.stopPropagation();
              event.preventDefault();
              setSelected(null);
              setId(null);
            }}
          />
          <div className="image_container">
            <Image
              src={selected?.main_image?.image}
              alt={"Image"}
              width={170}
              height={120}
            />
          </div>
          <div className="detail_right_container">
            <h3>{capitalizeFirstLetter(selected?.title)} </h3>
            <h2>
              Price : {selected.financials?.price}{" "}
              {selected?.financials?.currency}
            </h2>
            {selected?.address && <p>{selected?.address}</p>}
          </div>
        </div>
      </Link>
    </InfoWindow>
  );
}

export default CustomMarker;
