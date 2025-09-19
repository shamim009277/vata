$(function() {
	"use strict";
	new PerfectScrollbar(".header-message-list"), new PerfectScrollbar(".header-notifications-list"), $(".mobile-search-icon").on("click", function() {
		$(".search-bar").addClass("full-search-bar")
	}), $(".search-close").on("click", function() {
		$(".search-bar").removeClass("full-search-bar")
	}), $(".mobile-toggle-menu").on("click", function() {
		$(".wrapper").addClass("toggled")
	}), $(".toggle-icon").click(function() {
		$(".wrapper").hasClass("toggled") ? ($(".wrapper").removeClass("toggled"), $(".sidebar-wrapper").unbind("hover")) : ($(".wrapper").addClass("toggled"), $(".sidebar-wrapper").hover(function() {
			$(".wrapper").addClass("sidebar-hovered")
		}, function() {
			$(".wrapper").removeClass("sidebar-hovered")
		}))
	}), $(document).ready(function() {
		$(window).on("scroll", function() {
			$(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut()
		}), $(".back-to-top").on("click", function() {
			return $("html, body").animate({
				scrollTop: 0
			}, 600), !1
		})
	}), $(function() {
		for (var e = window.location, o = $(".metismenu li a").filter(function() {
				return this.href == e
			}).addClass("").parent().addClass("mm-active"); o.is("li");) o = o.parent("").addClass("mm-show").parent("").addClass("mm-active")
	}), $(function() {
		$("#menu").metisMenu()
	}), $(".chat-toggle-btn").on("click", function() {
		$(".chat-wrapper").toggleClass("chat-toggled")
	}), $(".chat-toggle-btn-mobile").on("click", function() {
		$(".chat-wrapper").removeClass("chat-toggled")
	}), $(".email-toggle-btn").on("click", function() {
		$(".email-wrapper").toggleClass("email-toggled")
	}), $(".email-toggle-btn-mobile").on("click", function() {
		$(".email-wrapper").removeClass("email-toggled")
	}), $(".compose-mail-btn").on("click", function() {
		$(".compose-mail-popup").show()
	}), $(".compose-mail-close").on("click", function() {
		$(".compose-mail-popup").hide()
	}), $(".switcher-btn").on("click", function() {
		$(".switcher-wrapper").toggleClass("switcher-toggled")
	}), $(".close-switcher").on("click", function() {
		$(".switcher-wrapper").removeClass("switcher-toggled")
	}), $("#lightmode").on("click", function() {
		$("html").attr("class", "light-theme")
	}), $("#darkmode").on("click", function() {
		$("html").attr("class", "dark-theme")
	}), $("#semidark").on("click", function() {
		$("html").attr("class", "semi-dark")
	}), $("#minimaltheme").on("click", function() {
		$("html").attr("class", "minimal-theme")
	}), $("#headercolor1").on("click", function() {
		$("html").addClass("color-header headercolor1"), $("html").removeClass("headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
	}), $("#headercolor2").on("click", function() {
		$("html").addClass("color-header headercolor2"), $("html").removeClass("headercolor1 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
	}), $("#headercolor3").on("click", function() {
		$("html").addClass("color-header headercolor3"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
	}), $("#headercolor4").on("click", function() {
		$("html").addClass("color-header headercolor4"), $("html").removeClass("headercolor1 headercolor2 headercolor3 headercolor5 headercolor6 headercolor7 headercolor8")
	}), $("#headercolor5").on("click", function() {
		$("html").addClass("color-header headercolor5"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor3 headercolor6 headercolor7 headercolor8")
	}), $("#headercolor6").on("click", function() {
		$("html").addClass("color-header headercolor6"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor3 headercolor7 headercolor8")
	}), $("#headercolor7").on("click", function() {
		$("html").addClass("color-header headercolor7"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor3 headercolor8")
	}), $("#headercolor8").on("click", function() {
		$("html").addClass("color-header headercolor8"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor3")
	})



   // sidebar colors 
    $('#sidebarcolor1').click(theme1);
    $('#sidebarcolor2').click(theme2);
    $('#sidebarcolor3').click(theme3);
    $('#sidebarcolor4').click(theme4);
    $('#sidebarcolor5').click(theme5);
    $('#sidebarcolor6').click(theme6);
    $('#sidebarcolor7').click(theme7);
    $('#sidebarcolor8').click(theme8);

    function theme1() {
      $('html').attr('class', 'color-sidebar sidebarcolor1');
    }

    function theme2() {
      $('html').attr('class', 'color-sidebar sidebarcolor2');
    }

    function theme3() {
      $('html').attr('class', 'color-sidebar sidebarcolor3');
    }

    function theme4() {
      $('html').attr('class', 'color-sidebar sidebarcolor4');
    }
	
	function theme5() {
      $('html').attr('class', 'color-sidebar sidebarcolor5');
    }
	
	function theme6() {
      $('html').attr('class', 'color-sidebar sidebarcolor6');
    }

    function theme7() {
      $('html').attr('class', 'color-sidebar sidebarcolor7');
    }

    function theme8() {
      $('html').attr('class', 'color-sidebar sidebarcolor8');
    }

});

// $(function () {
//     "use strict";

//     // === Apply saved settings from localStorage ===
//     let savedTheme = localStorage.getItem("theme") || "";
//     let savedHeaderColor = localStorage.getItem("headerColor") || "";
//     let savedSidebarColor = localStorage.getItem("sidebarColor") || "";

//     $("html").attr("class", `${savedTheme} ${savedHeaderColor} ${savedSidebarColor}`.trim());

//     // === UI Interactions ===
//     new PerfectScrollbar(".header-message-list");
//     new PerfectScrollbar(".header-notifications-list");

//     $(".mobile-search-icon").on("click", function () {
//         $(".search-bar").addClass("full-search-bar");
//     });

//     $(".search-close").on("click", function () {
//         $(".search-bar").removeClass("full-search-bar");
//     });

//     $(".mobile-toggle-menu").on("click", function () {
//         $(".wrapper").addClass("toggled");
//     });

//     $(".toggle-icon").click(function () {
//         if ($(".wrapper").hasClass("toggled")) {
//             $(".wrapper").removeClass("toggled");
//             $(".sidebar-wrapper").unbind("hover");
//         } else {
//             $(".wrapper").addClass("toggled");
//             $(".sidebar-wrapper").hover(
//                 function () {
//                     $(".wrapper").addClass("sidebar-hovered");
//                 },
//                 function () {
//                     $(".wrapper").removeClass("sidebar-hovered");
//                 }
//             );
//         }
//     });

//     $(window).on("scroll", function () {
//         $(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut();
//     });

//     $(".back-to-top").on("click", function () {
//         $("html, body").animate({ scrollTop: 0 }, 600);
//         return false;
//     });

//     // === MetisMenu Activation ===
//     $("#menu").metisMenu();

//     // === Menu Active State ===
//     for (var e = window.location, o = $(".metismenu li a").filter(function () {
//         return this.href == e;
//     }).addClass("").parent().addClass("mm-active"); o.is("li");) {
//         o = o.parent("").addClass("mm-show").parent("").addClass("mm-active");
//     }

//     // === Chat & Email Panels ===
//     $(".chat-toggle-btn").on("click", function () {
//         $(".chat-wrapper").toggleClass("chat-toggled");
//     });

//     $(".chat-toggle-btn-mobile").on("click", function () {
//         $(".chat-wrapper").removeClass("chat-toggled");
//     });

//     $(".email-toggle-btn").on("click", function () {
//         $(".email-wrapper").toggleClass("email-toggled");
//     });

//     $(".email-toggle-btn-mobile").on("click", function () {
//         $(".email-wrapper").removeClass("email-toggled");
//     });

//     $(".compose-mail-btn").on("click", function () {
//         $(".compose-mail-popup").show();
//     });

//     $(".compose-mail-close").on("click", function () {
//         $(".compose-mail-popup").hide();
//     });

//     // === Theme Switcher ===
//     $(".switcher-btn").on("click", function () {
//         $(".switcher-wrapper").toggleClass("switcher-toggled");
//     });

//     $(".close-switcher").on("click", function () {
//         $(".switcher-wrapper").removeClass("switcher-toggled");
//     });

//     // === Theme Selection ===
//     $("#lightmode").on("click", function () {
//         updateHtmlClass("theme", "light-theme");
//     });

//     $("#darkmode").on("click", function () {
//         updateHtmlClass("theme", "dark-theme");
//     });

//     $("#semidark").on("click", function () {
//         updateHtmlClass("theme", "semi-dark");
//     });

//     $("#minimaltheme").on("click", function () {
//         updateHtmlClass("theme", "minimal-theme");
//     });

//     // === Header Colors ===
//     for (let i = 1; i <= 8; i++) {
//         $(`#headercolor${i}`).on("click", function () {
//             updateHtmlClass("header", `color-header headercolor${i}`);
//         });
//     }

//     // === Sidebar Colors ===
//     for (let i = 1; i <= 8; i++) {
//         $(`#sidebarcolor${i}`).on("click", function () {
//             updateHtmlClass("sidebar", `color-sidebar sidebarcolor${i}`);
//         });
//     }

//     // === Helper function to manage html classes ===
//     function updateHtmlClass(type, className) {
//         let html = $("html");
//         let currentClass = html.attr("class") || "";

//         let updatedClass = currentClass
//             .split(" ")
//             .filter(c => {
//                 if (type === "theme") return !c.endsWith("-theme") && c !== "semi-dark" && c !== "minimal-theme";
//                 if (type === "header") return !c.startsWith("headercolor") && c !== "color-header";
//                 if (type === "sidebar") return !c.startsWith("sidebarcolor") && c !== "color-sidebar";
//                 return true;
//             });

//         updatedClass.push(...className.split(" "));
//         html.attr("class", updatedClass.join(" ").trim());

//         // Store in localStorage
//         if (type === "theme") localStorage.setItem("theme", className);
//         if (type === "header") localStorage.setItem("headerColor", className);
//         if (type === "sidebar") localStorage.setItem("sidebarColor", className);
//     }
// });
