import { notifyError } from "../components/admin/comman/notification/Notification";

export const ucwords = (text) => {
    if (typeof text !== 'string' || text.length === 0) {
        return text;
    }

    return text.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

export const lowercase = (text) => {
    if (typeof text !== 'string' || text.length === 0) {
        return text;
    }

    return text
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toLowerCase() + word.slice(1))
        .join(' ');
};

export const rasc = (text) => {
    if (text) {
        const specialCharsRegex = /[!@#$%^&*(),.?":{}|<>-]/g;
        return text.replace(specialCharsRegex, '');
    }
}

export const formattedData = (formData) => {
    return Object.entries(formData).map(([key, value]) => ({
        propName: key,
        value: value
    }));
};

export const generateRandomString = () => {
    const length = Math.floor(Math.random() * (16 - 8 + 1)) + 8;
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    let generatedString = '';
    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        generatedString += charset[randomIndex];
    }

    return generatedString;
};

export const truncateString = (str, maxLength = 25) => {
    if (str && str.length > maxLength) {
        if(hasHTMLContent(str)){
            return  <span dangerouslySetInnerHTML={{ __html: str }}></span>
            // return `${text.slice(0, maxLength)}...`;
        }
        return `${str.slice(0, maxLength)}...`;
    }
    return str;
}

export const checkFileValidation = async (e) => {
    const file = e?.target?.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB limit

    if (!file) {
        notifyError('No file selected.');
        return false;
    }

    if (file.size > maxSize) {
        notifyError('File size exceeds the maximum limit of 5MB');
        return false;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg', 'image/web'];
    if (!allowedTypes.includes(file.type)) {
        notifyError('Invalid file type. Only JPEG and PNG are allowed.');
        return false;
    }

    return true;
};

export const transformData = (data) => {
    const transformed = {};

    Object.keys(data).forEach((key) => {
        const match = key.match(/^(\w+)\[(\d+)\]$/);

        if (match) {
            const field = match[1];
            const index = parseInt(match[2]);

            if (!transformed[field]) transformed[field] = [];

            transformed[field][index] = data[key];
        } else {
            transformed[key] = data[key];
        }
    });

    return transformed;
};

export const debounce = (func, wait) => {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

export const loadCSS = (url) => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = url;
    document.head.appendChild(link);
};

export const loadScript = (url) => {
    const script = document.createElement('script');
    script.src = url;
    script.async = true;
    document.body.appendChild(script);
};

export const customStyles = {
    control: (provided, state) => ({
        ...provided,
        borderRadius: `var(--bs-border-radius-xxl)`,
        boxShadow: state.isFocused ? `var(--background)` : `none`,
    }),
    option: (provided, state) => ({
        ...provided,
        textAlign: `left`,
        color: state.isSelected ? `var(--white)` : `var(--light)`,
        '& i': {
            color: state.isSelected ? `var(--white)` : `var(--light)`,
        }
    }),
    dropdownIndicator: (provided) => ({
        ...provided,
        color: `var(--light)`,
    }),
    clearIndicator: (provided) => ({
        ...provided,
        color: `var(--light)`,
    }),
    menuList: (provided) => ({
        ...provided,
        animation: 'slideIn 0.5s ease-in-out forwards',
        '&::-webkit-scrollbar': {
            display: 'none',
        },
        scrollbarWidth: 'none',
    }),
};

export const fetchSelectedOptions = (data) => {
    const Options = data?.map((val, index) => ({
        value: val?.id,
        label: `${ucwords(val?.name)}`
    }));
    return Options;
}

export const getFullName = (name) => {
    return `${name?.first_name} ${name?.middle_name ? name?.middle_name + ' ' : ''}${name?.last_name}`;
}

export const dummyImage = () => {
    return `./../../public/admin/img/profile-img.jpg`;
}

export const handleErrorImage = (event, defaultImage) => {
    return event.target.src = defaultImage ?? dummyImage();
};

export const loremPatterns = [
    /lorem ipsum/i,  // Match "Lorem ipsum" (case insensitive)
    /dolor sit amet/i,  // Match "dolor sit amet"
    /consectetur adipiscing elit/i,  // Match "consectetur adipiscing elit"
    /sed do eiusmod tempor incididunt/i,  // Match "sed do eiusmod tempor incididunt"
    /ut labore et dolore magna aliqua/i,  // Match "ut labore et dolore magna aliqua"
    /quis nostrud exercitation ullamco/i,  // Match "quis nostrud exercitation ullamco"
    /labore et dolore magna aliqua/i,  // Match "labore et dolore magna aliqua"
];

export const loremTextCheck = (text) => {
    return loremPatterns.some((pattern) => pattern.test(text));

    // if (isLorem) {
    //     return res.status(400).json({ error: 'Error: Please do not use Lorem Ipsum text.' });
    // }
}

export const handleToggleSidebar = () => {
    document.body.classList.toggle('toggle-sidebar');
    const element = document.getElementById('sidebar');
    if (element) {
        if (window.innerWidth >= 1020) {
            element.classList.toggle('my-3');
            element.classList.toggle('mx-2');
        } else {
            console.clear();
        }
    }

    const isSidebarToggled = document.body.classList.contains('toggle-sidebar');
    console.info(`Sidebar Toggle is ${isSidebarToggled ? 'ON' : 'OFF'}`);
}

export const hexToRGBA = (hex, alpha = 1) => {
    hex = hex.replace('#', '');
    let r = parseInt(hex.substring(0, 2), 16);
    let g = parseInt(hex.substring(2, 4), 16);
    let b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

export const hexToRGB = (hex) => {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(char => char + char).join('');
    const bigint = parseInt(hex, 16);
    return {
        r: (bigint >> 16) & 255,
        g: (bigint >> 8) & 255,
        b: bigint & 255
    };
}

export const luminance = (r, g, b) => {
    const a = [r, g, b].map(v => {
        v /= 255;
        return v <= 0.03928
            ? v / 12.92
            : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
}

export const contrast = (rgb1, rgb2) => {
    const lum1 = luminance(rgb1.r, rgb1.g, rgb1.b),
        lum2 = luminance(rgb2.r, rgb2.g, rgb2.b);
    return (Math.max(lum1, lum2) + 0.05) /
        (Math.min(lum1, lum2) + 0.05);
}

export const pickReadableTextColor = (bgHex, colorOptions = ['#ffffff', '#f9f9f9', '#f1f1f1', '#cccccc', '#999999', '#666666', '#333333', '#000000', '#fffae3', '#f5f5dc', '#ffe4e1', '#e6f7ff', '#e8f5e9', '#fce4ec', '#fafafa', '#1a1a1a']) => {
    const bgRGB = hexToRGB(bgHex);
    let bestColor = '#000',
        bestContrast = 0;

    for (const color of colorOptions) {
        const fgRGB = hexToRGB(color),
            currentContrast = contrast(bgRGB, fgRGB);
        if (currentContrast > bestContrast) {
            bestContrast = currentContrast;
            bestColor = color;
        }
    }

    return bestColor;
}

export const hasHTMLContent = (html) => {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  return Array.from(doc.body.childNodes).some(node => node.nodeType === 1); // 1 = ELEMENT_NODE
}