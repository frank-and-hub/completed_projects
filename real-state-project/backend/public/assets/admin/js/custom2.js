function propertyAddress(
    address = {},
    suburb = "",
    town = "",
    province = "",
    country = ""
) {
    // console.log(address);
    return `${address?.streetNumber ? address?.streetNumber + " " : ""}${
        address?.streetName ? address?.streetName + " " : ""
    }${address?.unitNumber ? address?.unitNumber + " " : ""}${
        address?.complexName ? address?.complexName + " " : ""
    }${suburb ? suburb + ", " : ""}${town ? town + ", " : ""}${
        province ? province + ", " : ""
    }${country ? country : ""}`;
}

function R_price(price) {
    return `R ${numberFormat(price)}`;
}

function numberFormat(number, currencyCode = "ZAR") {
    let formatted;
    if (Math.floor(number) === number) {
        formatted = new Intl.NumberFormat("fr-ZA", {
            currency: currencyCode,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(number);
    } else {
        formatted = new Intl.NumberFormat("fr-ZA", {
            currency: currencyCode,
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(number);
    }
    formatted = formatted.replace(/,/g, " ").replace(/\./g, ",");
    return formatted;
}

function findMainImage(data) {
    let mainImage = data.find((image) => image?.isMain === 1);
    return mainImage ? mainImage.path : null;
}

function truncateTitle(string, number) {
    if (string.length > 0) {
        if (string.length > number) {
            return string.substring(0, number) + "...";
        }
    }
    return string;
}

function findGroupUser(data) {
    const groupedByUserId = {};
    data.forEach((property) => {
        const userId = property.user_id;

        if (!groupedByUserId[userId]) {
            groupedByUserId[userId] = [];
        }
        groupedByUserId[userId].push(property);
    });
    const dataCount = Object.keys(groupedByUserId).length;
    return dataCount;
}

function dateF2(date) {
    const d = new Date(date);
    const day = d.getDate();
    const month = d.toLocaleString("default", { month: "long" });
    const year = d.getFullYear();
    const time = d
        .toLocaleTimeString([], {
            hour: "numeric",
            minute: "numeric",
            hour12: true,
        })
        .toUpperCase();

    let suffix = "th";
    if (day % 10 === 1 && day !== 11) {
        suffix = "st";
    } else if (day % 10 === 2 && day !== 12) {
        suffix = "nd";
    } else if (day % 10 === 3 && day !== 13) {
        suffix = "rd";
    }
    const formattedDate = `${day}${suffix} ${month} ${year} ${time}`;
    return formattedDate;
}

function dateF(date) {
    const d = new Date(date);

    const day = d.getDate();
    const month = d.toLocaleString("default", { month: "long" });
    const year = d.getFullYear();

    let suffix = "th";
    if (day % 10 === 1 && day !== 11) {
        suffix = "st";
    } else if (day % 10 === 2 && day !== 12) {
        suffix = "nd";
    } else if (day % 10 === 3 && day !== 13) {
        suffix = "rd";
    }
    const formattedDate = `${day}${suffix} ${month} ${year}`;
    return formattedDate;
}

function timeF(date) {
    const d = new Date(date);
    const time = d
        .toLocaleTimeString([], {
            hour: "numeric",
            minute: "numeric",
            hour12: true,
        })
        .toUpperCase();
    return `${time}`;
}

function findEventType(eventDateString) {
    if (!eventDateString) {
        return 2;
    } else {
        const eventDate = new Date(eventDateString);
        const currentDate = new Date();
        if (eventDate < currentDate) {
            return 1;
        } else {
            return 0;
        }
    }
}

$(".select2").select2();

function utcTimeConversion(dateStr, timeStr) {
    // Convert "DD-MM-YYYY" to "YYYY-MM-DD"
    const [d, m, y] = dateStr.trim().split("-");
    const formatted = `${y}-${m}-${d}T${timeStr.trim()}:00`;

    const localDateTime = new Date(formatted);

    console.log("Raw input:", dateStr, timeStr);
    console.log("Formatted ISO string:", formatted);
    console.log("Parsed Local DateTime:", localDateTime);

    // Check if valid date
    if (isNaN(localDateTime.getTime())) {
        return {
            error: "Invalid date or time format",
            default_date: dateStr,
            default_time: timeStr,
        };
    }

    // Removed unused variable utcDateTime
    const utcDate = localDateTime.toISOString().split("T")[0];
    const utcTime = localDateTime.toISOString().split("T")[1].substring(0, 5);
    let date = {
        date: utcDate,
        time: utcTime,
        default_date: dateStr,
        default_time: timeStr,
    };
    return date;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function statusBtn(status, event_datetime) {
    let btn = "";
    let isExpiry = isExpiredEvent(event_datetime);
    switch (status) {
        case "pending":
            btn =
                "<div class='badge badge-pill badge-light pvr-status-badge " +
                (isExpiry ? "Expired" : "Pending") +
                "' style='background:" +
                (isExpiry ? "#6c757d" : "#17a2b8") +
                "'> <div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" +
                capitalize(status) +
                "</p> </div>  </div>";
            break;
        case "accepted":
            btn =
                "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#0087ff'> <div class='d-flex align-item-center justify-content-center'> <p class='mb-0 text-white small font-weight-bold'>" +
                capitalize(status) +
                "</p> </div> </div>";
            break;
        case "completed":
            btn =
                "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#04b76b'><div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" +
                capitalize(status) +
                "</p> </div></div>";
            break;
        default:
            btn =
                "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#dc3545'><div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" +
                capitalize(status) +
                "</p></div></div>";
            break;
    }

    return btn;
}

function isExpiredEvent(event_datetime) {
    const eventDate = new Date(event_datetime);
    const currentDate = new Date();
    if (eventDate < currentDate) {
        return true;
    } else {
        return false;
    }
}

function getCurrencySymbol(currency) {
    try {
        return (0)
            .toLocaleString("en", {
                style: "currency",
                currency: currency,
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            })
            .replace(/\d/g, "")
            .trim();
    } catch (error) {
        return "R";
    }
}

function isLoremIpsum(text) {
    const loremPatterns = [
        /\b(lorem|ipsum|dolor|sit|amet|consectetur|adipiscing|elit)\b/i,
        /\b(sed|do|eiusmod|tempor|incididunt|ut|labore|et|dolore|magna|aliqua)\b/i,
        /\b(quis|nostrud|exercitation|ullamco|laboris|nisi|aliquip|ex|ea|commodo)\b/i,
    ];

    const cleanedText = text.trim().toLowerCase();

    if (cleanedText.length < 10) {
        return false;
    }

    let matchCount = 0;
    loremPatterns.forEach((pattern) => {
        if (pattern.test(cleanedText)) {
            matchCount++;
        }
    });
    return matchCount >= 2;
}

$("input.check-lorem, textarea.check-lorem").on("input", function () {
    const $this = $(this);
    const text = $this.val();

    if (isLoremIpsum(text)) {
        $this.addClass("is-lorem");
        if (!$this.next(".lorem-warning").length) {
            $this.after(
                '<span class="lorem-warning text-danger">Lorem Ipsum detected!</span>'
            );
        }
    } else {
        $this.removeClass("is-lorem");
        $this.next(".lorem-warning").remove();
    }
});

$("form").on("keypress", function (e) {
    if (e.which === 13 && e.target.tagName !== "TEXTAREA") {
        e.preventDefault();
    }
});

  const input = $('input[name=search]');
  const icon = $('.search-icon i');

  input.on('input', function () {
    if ($(this).val().trim() !== '') {
      icon.removeClass('fa-search').addClass('fa-times');
    } else {
      icon.removeClass('fa-times').addClass('fa-search');
    }
  });

  $('.search-icon').on('click', function () {
    if (icon.hasClass('fa-times')) {
      input.val('');
      input.trigger('input');
      input.trigger('keyup');
      icon.removeClass('fa-times').addClass('fa-search');
      input.focus();
    }
  });
