import { AsyncPaginate } from "react-select-async-paginate";
import useGetQuerySearch from "./useGetQuerySearch";
import { useEffect, useState } from "react";
import "./customApiSelect.style.scss";
import CustomText from "../customText/CustomText";

// For 'api' type
interface ICustomApiSelectAPI {
  queryFn: (params: pageSearchOptions) => Promise<any>;
  data?: never;
  isMulti?: boolean;
  closeMenuOnSelect?: boolean;
  className?: string;
  placeholder?: string;
  label?: string;
  isDisabled?: boolean;
  mb?: number | keyof typeof spacingMap;
  pb?: number | keyof typeof spacingMap;
  pt?: number | keyof typeof spacingMap;
  px?: number | keyof typeof spacingMap;
  py?: number | keyof typeof spacingMap;
  mx?: number | keyof typeof spacingMap;
  my?: number | keyof typeof spacingMap;
  error?: string; // New error prop for error message
  onChange?: (
    selectedValue: readonly string[] | string | null,
    additionalData?: any
  ) => void; // onChange callback to return selected value
  // value?: readonly string[] | string | number | null; // New prop to accept an external value
  defaultOptions?: boolean; // default APi Call
  apiEnabled?: boolean; // APi Call to enable API
  labelKey?: string; //
  filterDataLogic?: any; // Filter option data
  externalValue?: SelectOptionType | readonly SelectOptionType[] | null;
  required?: boolean;
  value?: string;
}

// Mapping Mantine spacing sizes to rem values
const spacingMap = {
  xs: "0.5rem",
  sm: "1rem",
  md: "1.5rem",
  lg: "2rem",
  xl: "3rem",
};

// Helper function to convert spacing props to rem values or retain number values
const getSpacingValue = (value: number | string | undefined) => {
  if (typeof value === "number") {
    return `${value}rem`; // If a number is passed, convert it to rem
  }
  return spacingMap[value as keyof typeof spacingMap] || value; // If string, map or use it directly
};

const customClassNames = {
  input: () => "mantine-Input-input mantine-Select-input",
  control: () => "mantine-Input-control",
  menu: () => "mantine-Select-menu",
  option: () => "mantine-Select-option",
  placeholder: () => "mantine-Select-label",
};

function CustomApiSelect({
  queryFn,
  isMulti,
  closeMenuOnSelect,
  className,
  placeholder,
  label,
  mb,
  pb,
  pt,
  px,
  py,
  mx,
  my,
  error, // Error message
  onChange: returnOnChangeValue,
  externalValue, // New value prop
  isDisabled,
  defaultOptions = true,
  apiEnabled = true,
  labelKey = "name",
  filterDataLogic,
  required = false,
  value: est,
}: ICustomApiSelectAPI) {
  const [value, setValue] = useState<
    readonly SelectOptionType[] | SelectOptionType | null
  >(isMulti ? [] : null);
  useEffect(() => {
    if (!est) {
      setValue(null);
    }
  }, [est]);
  // Update local state when external value changes
  // Update local state when external value changes

  const { loadOptions } = useGetQuerySearch(labelKey, queryFn, apiEnabled);
  useEffect(() => {
    if (!externalValue) {
      return;
    }

    if (Array.isArray(externalValue)) {
      setValue(externalValue);
      handleChange(externalValue);
    } else {
      setValue(externalValue);
      handleChange(externalValue);

      // const target = apiData?.find((item) => externalValue == item?.value);
      // target && setValue(target); // Convert single string to array
    }
  }, [externalValue]);
  // const valueContainerRef = useRef<any>(null);

  // Apply Mantine-like spacing using rem values
  const dynamicStyles = {
    marginBottom: getSpacingValue(mb),
    paddingBottom: getSpacingValue(pb),
    paddingTop: getSpacingValue(pt),
    paddingLeft: getSpacingValue(px),
    paddingRight: getSpacingValue(px),
    marginTop: getSpacingValue(my),
    marginLeft: getSpacingValue(mx),
    marginRight: getSpacingValue(mx),
  };

  const handleChange = (selectValue: any) => {
    let selectedValue: SelectOptionType | SelectOptionType[] | null =
      selectValue;
    if (isMulti) {
      setTimeout(() => {
        // Find the closest parent element that contains the class of the currently focused select input
        const focusedContainer = document?.activeElement?.closest(
          ".custom-select__value-container--has-value"
        );

        // Scroll the focused element to the bottom
        if (focusedContainer) {
          focusedContainer.scrollTop = focusedContainer.scrollHeight;
        }
      }, 50);
    }

    setValue(selectedValue); // Update local state
    if (returnOnChangeValue) {
      let returnValue = selectValue;

      if (Array.isArray(selectedValue)) {
        returnValue = selectedValue?.map((item) => item?.value);
      } else if (typeof selectedValue === "object") {
        returnValue = selectedValue?.value;
      }
      returnOnChangeValue(returnValue, selectedValue);
    }
  };

  return (
    <div
      className={`mantine-InputWrapper-root ${className}`}
      style={dynamicStyles}
    >
      {label && (
        <>
          <label className="mantine-Select-label custom-label-text">
            {label}
          </label>
          {required && <span style={{ color: "red" }}> *</span>}
        </>
      )}

      <AsyncPaginate
        isDisabled={isDisabled}
        defaultOptions={defaultOptions}
        value={value}
        loadOptions={loadOptions}
        isMulti={isMulti}
        closeMenuOnSelect={closeMenuOnSelect ?? isMulti ? false : true}
        onChange={handleChange}
        classNamePrefix={"custom-select"}
        className={`mantine-InputWrapper-root mantine-Select-root async-custom-select ${className} ${
          error ? "has-error" : ""
        }`}
        placeholder={placeholder}
        // isClearable
        debounceTimeout={300}
        classNames={customClassNames}
        filterOption={filterDataLogic}
        // hideSelectedOptions={false}
        // components={{
        //   ValueContainer,
        // }}

        styles={{
          menuPortal: (provided) => ({ ...provided, zIndex: 9999 }),
          menu: (provided) => ({
            ...provided,
            zIndex: 9999,
            fontFamily: "sans-serif",
            fontSize: "12px",
            borderRadius: "0px",
            textTransform: "capitalize",
            overflow: "hidden",
            maxHeight: 200,
          }),
        }}
        menuPortalTarget={document.body}
      />
      {error && (
        <CustomText className="custom-select__error-text">{error}</CustomText>
      )}
    </div>
  );
}

export default CustomApiSelect;

// const ValueContainer = ({ children, ...props }: any) => {
//   return (
//     <div className="custom-select_component__value-container--has-value">
//       <components.ValueContainer {...props}>
//         {children}
//       </components.ValueContainer>
//     </div>
//   );
// };
