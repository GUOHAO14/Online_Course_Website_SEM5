let bookmark = document.getElementsByClassName("inProgressBookmark");
for (let i = 0; i < bookmark.length; i++) {
    bookmark[i].addEventListener("click", function (event) { 
        event.stopPropagation(); //prevents parent click
    });
}

let bookmark2 = document.getElementsByClassName("bookmark");
for (let i = 0; i < bookmark2.length; i++) {
    bookmark2[i].addEventListener("click", function (event) {
        event.stopPropagation(); //prevents parent click
    });
}

document.querySelectorAll('.courseMain').forEach(main => {
    main.addEventListener('mouseenter', () => {
        main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
    });
    main.addEventListener('mouseleave', () => {
        main.closest('.courseCard').style.boxShadow = 'none';
    });
});

bookmarks.forEach(bookmark => {
    // Initial styles (matches your .bookmark CSS)
    bookmark.style.backgroundColor = "rgb(41, 41, 255)";
    bookmark.style.cursor = "pointer";
    bookmark.style.transition = "all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)";
    bookmark.style.borderRadius = "0px 0px 4px 4px";
    bookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";

    // Set a custom property to track "ticked" status
    bookmark.dataset.ticked = "false";

    // On hover — simulate :hover
    bookmark.addEventListener('mouseenter', () => {
        bookmark.style.backgroundColor = "rgb(71, 71, 255)";
        bookmark.style.transform = "scale(1.1)";
        bookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
    });

    // On mouse leave — undo hover effect
    bookmark.addEventListener('mouseleave', () => {
        bookmark.style.backgroundColor = "rgb(41, 41, 255)";
        bookmark.style.transform = "scale(1)";
        bookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";
    });

    // On click (simulate :active)
    bookmark.addEventListener('mousedown', () => {
        bookmark.style.transform = "scale(0.95)";
        bookmark.style.boxShadow = "0 1px 4px rgba(0, 0, 0, 0.2)";
    });
    

    // On mouse up (return to hover style)
    bookmark.addEventListener('mouseup', () => {
        bookmark.style.transform = "scale(1.1)";
        bookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
    
    });
});


inProgressBookmarks.forEach(inProgressBookmark => {
    // Initial styles (matches your .inProgressBookmark CSS)
    inProgressBookmark.style.backgroundColor = "rgb(41, 41, 255)";
    inProgressBookmark.style.cursor = "pointer";
    inProgressBookmark.style.transition = "all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)";
    inProgressBookmark.style.borderRadius = "0px 0px 4px 4px";
    inProgressBookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";

    // On hover — simulate :hover
    inProgressBookmark.addEventListener('mouseenter', () => {
        inProgressBookmark.style.backgroundColor = "rgb(71, 71, 255)";
        inProgressBookmark.style.transform = "scale(1.1)";
        inProgressBookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
    });

    // On mouse leave — undo hover effect
    inProgressBookmark.addEventListener('mouseleave', () => {
        inProgressBookmark.style.backgroundColor = "rgb(41, 41, 255)";
        inProgressBookmark.style.transform = "scale(1)";
        inProgressBookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";
    });

    // On click (simulate :active)
    inProgressBookmark.addEventListener('mousedown', () => { 
        inProgressBookmark.style.transform = "scale(0.95)";
        inProgressBookmark.style.boxShadow = "0 1px 4px rgba(0, 0, 0, 0.2)";

    });

    // On mouse up (return to hover style)
    inProgressBookmark.addEventListener('mouseup', () => {
        inProgressBookmark.style.transform = "scale(1.1)";
        inProgressBookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
    });
});