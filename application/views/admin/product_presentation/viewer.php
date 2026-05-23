<!DOCTYPE html>
<html>

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
   <title><?= $presentation->title ?></title>

   <style>
      :root {
         --primary-color: rgba(13, 87, 46, 1);
         --secondary-color: rgba(253, 201, 0, 1);
         --gradient: linear-gradient(90deg, var(--primary-color) 35%, var(--secondary-color) 100%);
         --sidebar-width: 220px;
         --sidebar-collapsed-width: 50px;
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         background: var(--gradient);
         background-size: 200% 200%;
         animation: colorSwap 5s ease-in-out infinite;
         height: 100vh;
         margin: 0;
         overflow: hidden !important;
         font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      }

      .container {
         display: flex;
         height: 100vh;
         position: relative;
      }

      /* Sidebar Styles */
      .sidebar {
         width: var(--sidebar-width);
         background: rgba(255, 255, 255, 0.95);
         height: 100vh;
         transition: all 0.3s ease;
         position: relative;
         box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      }

      .sidebar.collapsed {
         width: var(--sidebar-collapsed-width);
      }

      .sidebar-toggle {
         position: absolute;
         right: -15px;
         top: 50%;
         transform: translateY(-50%);
         background: white;
         border: 1px solid #ddd;
         border-radius: 50%;
         width: 30px;
         height: 30px;
         cursor: pointer;
         z-index: 10;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      }

      .thumbnail-container {
         height: 100%;
         overflow-y: auto;
         padding: 15px;
         scrollbar-width: thin;
      }

      /* Thumbnails */
      .thumbnail {
         width: 100%;
         margin-bottom: 15px;
         cursor: pointer;
         border: 2px solid transparent;
         border-radius: 8px;
         transition: all 0.3s;
         overflow: hidden;
         position: relative;
      }

      .thumbnail:hover {
         transform: translateY(-2px);
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .thumbnail.active {
         border-color: var(--primary-color);
         box-shadow: 0 0 0 2px var(--primary-color);
      }

      .thumbnail img,
      .thumbnail canvas {
         width: 100%;
         height: auto;
         display: block;
      }

      /* Main Content Area */
      .main-content {
         flex: 1;
         display: flex;
         flex-direction: column;
         padding: 20px;
         position: relative;
      }

      .presentation-container {
         flex: 1;
         background: white;
         border-radius: 15px;
         position: relative;
         display: flex;
         align-items: center;
         justify-content: center;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }

      .current-slide {
         max-width: 100%;
         max-height: 100%;
         display: flex;
         justify-content: center;
         align-items: center;
         padding: 20px;
      }

      .current-slide canvas {
         max-width: 100%;
         max-height: calc(100vh - 140px);
         object-fit: contain;
      }

      /* Navigation Controls */
      .nav-controls {
         position: absolute;
         bottom: 30px;
         left: 50%;
         transform: translateX(-50%);
         display: flex;
         align-items: center;
         background: rgba(0, 0, 0, 0.7);
         padding: 5px 5px;
         border-radius: 30px;
         z-index: 100;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      }

      .nav-button {
         background: none;
         border: none;
         color: white;
         font-size: 24px;
         cursor: pointer;
         padding: 0 15px;
         transition: all 0.2s;
      }

      .nav-button:hover {
         transform: scale(1.1);
      }

      .slide-number {
         color: white;
         margin: 0 10px;
         font-size: 16px;
         font-weight: 500;
      }

      /* Navigation Arrows */
      .nav-arrow {
         position: absolute;
         top: 50%;
         transform: translateY(-50%);
         background: rgba(0, 0, 0, 0.7);
         color: white;
         width: 40px;
         height: 40px;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         z-index: 100;
         border: none;
         border-radius: 50%;
         transition: all 0.2s;
         font-size: 20px;
      }

      .nav-arrow:hover {
         background: rgba(0, 0, 0, 0.9);
         transform: translateY(-50%) scale(1.1);
      }

      .prev-slide {
         left: 20px;
      }

      .next-slide {
         right: 20px;
      }

      /* Fullscreen Button */
      .fullscreen-btn {
         position: absolute;
         top: 20px;
         right: 20px;
         background: rgba(0, 0, 0, 0.7);
         border: none;
         color: white;
         width: 40px;
         height: 40px;
         border-radius: 50%;
         cursor: pointer;
         z-index: 100;
         display: flex;
         align-items: center;
         justify-content: center;
         transition: all 0.2s;
      }

      .fullscreen-btn:hover {
         background: rgba(0, 0, 0, 0.9);
         transform: scale(1.1);
      }

      /* Loading Spinner */
      .loading-spinner {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(255, 255, 255, 0.9);
         display: flex;
         justify-content: center;
         align-items: center;
         z-index: 1000;
      }

      .spinner {
         width: 50px;
         height: 50px;
         border: 5px solid #f3f3f3;
         border-top: 5px solid var(--primary-color);
         border-radius: 50%;
         animation: spin 1s linear infinite;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
         .sidebar {
            position: fixed;
            left: 0;
            z-index: 1000;
            transform: translateX(-100%);
         }

         .sidebar.active {
            transform: translateX(0);
         }

         .main-content {
            padding: 10px;
         }

         .nav-controls {
            bottom: 20px;
            padding: 8px 15px;
         }

         .nav-arrow {
            width: 35px;
            height: 35px;
            font-size: 16px;
         }

         .slide-number {
            font-size: 14px;
            margin: 0 15px;
         }
      }

      .dt-loader-logo {
         transform: translateZ(1px);
         display: flex;
         flex-direction: column;
         align-items: center
      }

      .dt-loader-logo:after {
         content: '';
         display: inline-block;
         width: 48px;
         height: 48px;
         background: url(<?= get_favicon_link(); ?>) no-repeat center center;
         background-size: cover;
         box-sizing: border-box;
         box-shadow: 2px 2px 2px 1px rgb(0 0 0 / .1);
         animation: logo-flip 1s linear infinite
      }

      .dt-loader-logo span {
         margin-top: 10px;
         font-size: 16px;
         font-weight: 700;
         color: #333
      }

      @keyframes logo-flip {
         0% {
            transform: rotateY(0deg)
         }

         100% {
            transform: rotateY(360deg)
         }
      }

      /* Animations */
      @keyframes colorSwap {

         0%,
         100% {
            background-position: 0% 50%;
         }

         50% {
            background-position: 100% 50%;
         }
      }

      @keyframes spin {
         0% {
            transform: rotate(0deg);
         }

         100% {
            transform: rotate(360deg);
         }
      }
   </style>
</head>

<body>
   <div id="loading" class="loading-spinner">
      <div id="loading-spinner" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;">
         <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="dt-loader-logo"><span></span></div>
         </div>
      </div>
   </div>

   <div class="container">
      <div class="sidebar" id="sidebar">
         <button class="sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle">❮</button>
         <div class="thumbnail-container" id="thumbnailContainer"></div>
      </div>
      <div class="main-content">
         <div class="presentation-container">
            <button class="fullscreen-btn" onclick="toggleFullscreen()">⤢</button>
            <button class="nav-arrow prev-slide" onclick="prevSlide()">❮</button>
            <button class="nav-arrow next-slide" onclick="nextSlide()">❯</button>
            <div class="current-slide" id="currentSlide"></div>
            <div class="nav-controls">
               <button class="nav-button" onclick="prevSlide()">❮</button>
               <span class="slide-number" id="slideNumber">1 / 1</span>
               <button class="nav-button" onclick="nextSlide()">❯</button>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
   <script>
      let currentPageNum = 1;
      let pdfDocument = null;
      const pdfUrl = "<?= $pdf_url ?>";
      let sidebarCollapsed = false;
      let isFullscreen = false;

      document.addEventListener('contextmenu', function(e) {
         e.preventDefault();
      });

      document.addEventListener('keydown', function(e) {
         if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
         }

         if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C' || e.key === 'V') || e.key === 'F12') {
            e.preventDefault();
         }

         if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
         }
      });

      document.addEventListener('DOMContentLoaded', async function() {
         pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

         try {
            pdfDocument = await pdfjsLib.getDocument(pdfUrl).promise;
            await loadThumbnails();
            await showPage(1);
            updateSlideNumber();
            document.getElementById('loading').style.display = 'none';
         } catch (error) {
            console.error('Error loading PDF:', error);
            document.getElementById('loading').innerHTML = '<p>Error loading PDF. Please try again.</p>';
         }
      });

      function toggleSidebar() {
         const sidebar = document.getElementById('sidebar');
         const toggleBtn = document.getElementById('sidebarToggle');
         sidebarCollapsed = !sidebarCollapsed;

         sidebar.classList.toggle('collapsed');
         toggleBtn.innerHTML = sidebarCollapsed ? '❯' : '❮';
      }

      async function loadThumbnails() {
         const container = document.getElementById('thumbnailContainer');
         container.innerHTML = '';
         const numPages = pdfDocument.numPages;

         for (let i = 1; i <= numPages; i++) {
            const thumb = document.createElement('div');
            thumb.className = 'thumbnail';
            thumb.setAttribute('data-page', i);
            thumb.onclick = () => showPage(i);

            const canvas = document.createElement('canvas');
            const page = await pdfDocument.getPage(i);
            const viewport = page.getViewport({
               scale: 0.5 // Keep thumbnail scale low for performance
            });

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
               canvasContext: canvas.getContext('2d'),
               viewport: viewport,
            }).promise;

            thumb.appendChild(canvas);
            container.appendChild(thumb);
         }
      }

      async function showPage(pageNum) {
         if (pageNum < 1 || pageNum > pdfDocument.numPages) return;

         const container = document.getElementById('currentSlide');
         container.innerHTML = '';

         try {
            currentPageNum = pageNum;
            const page = await pdfDocument.getPage(pageNum);

            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.width = '100%';
            wrapper.style.height = '100%';
            wrapper.style.display = 'flex';
            wrapper.style.justifyContent = 'center';
            wrapper.style.alignItems = 'center';

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            const containerRect = container.getBoundingClientRect();
            const containerWidth = containerRect.width - 40;
            const containerHeight = containerRect.height - 40;

            const originalViewport = page.getViewport({
               scale: 1.0
            });

            // Set scale based on whether we're in fullscreen mode
            let scale;
            if (isFullscreen) {
               // Higher quality in fullscreen mode
               const scaleX = window.innerWidth / originalViewport.width;
               const scaleY = window.innerHeight / originalViewport.height;
               // For fullscreen, we'll use a higher base multiplier for better quality
               const baseMultiplier = 1.5; // Increase the base quality in fullscreen
               scale = Math.min(scaleX, scaleY, 3) * baseMultiplier; // Allow higher quality, up to 3x with improved base quality
            } else {
               // Regular quality in normal mode
               const scaleX = containerWidth / originalViewport.width;
               const scaleY = containerHeight / originalViewport.height;
               scale = Math.min(scaleX, scaleY, 2);
            }

            const viewport = page.getViewport({
               scale
            });

            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.display = 'block';

            const linksLayer = document.createElement('div');
            linksLayer.style.position = 'absolute';
            linksLayer.style.left = '0';
            linksLayer.style.top = '0';
            linksLayer.style.width = `${viewport.width}px`;
            linksLayer.style.height = `${viewport.height}px`;
            linksLayer.style.pointerEvents = 'none';

            const renderContext = {
               canvasContext: context,
               viewport: viewport,
               renderInteractiveForms: true,
               // Set image quality - higher in fullscreen
               imageQuality: isFullscreen ? 1.0 : 0.8
            };

            await page.render(renderContext).promise;

            const annotations = await page.getAnnotations();
            const textContent = await page.getTextContent();

            annotations.forEach(annotation => {
               if (annotation.subtype === 'Link' && annotation.url) {
                  const rect = viewport.convertToViewportRectangle(annotation.rect);
                  const [x1, y1, x2, y2] = rect;

                  const linkedTextItems = textContent.items.filter(item => {
                     const itemRect = viewport.convertToViewportRectangle(item.rect || [item.transform[4], item.transform[5], item.transform[4] + item.width, item.transform[5] + item.height]);
                     return doRectsOverlap(rect, itemRect);
                  });

                  let minX = Math.min(x1, x2);
                  let minY = Math.min(y1, y2);
                  let maxX = Math.max(x1, x2);
                  let maxY = Math.max(y1, y2);

                  linkedTextItems.forEach(item => {
                     const itemRect = viewport.convertToViewportRectangle(item.rect || [item.transform[4], item.transform[5], item.transform[4] + item.width, item.transform[5] + item.height]);
                     minX = Math.min(minX, itemRect[0]);
                     minY = Math.min(minY, itemRect[1]);
                     maxX = Math.max(maxX, itemRect[2]);
                     maxY = Math.max(maxY, itemRect[3]);
                  });

                  const linkElement = document.createElement('a');
                  linkElement.href = annotation.url;
                  linkElement.target = '_blank';
                  linkElement.rel = 'noopener noreferrer';
                  linkElement.style.position = 'absolute';
                  linkElement.style.left = `${minX + 70}px`;
                  linkElement.style.top = `${minY}px`;
                  linkElement.style.width = `${maxX - minX}px`;
                  linkElement.style.height = `${maxY - minY}px`;
                  linkElement.style.pointerEvents = 'auto';
                  linkElement.style.cursor = 'pointer';

                  linksLayer.appendChild(linkElement);
               }
            });

            function doRectsOverlap(rect1, rect2) {
               return !(rect1[0] > rect2[2] ||
                  rect2[0] > rect1[2] ||
                  rect1[1] > rect2[3] ||
                  rect2[1] > rect1[3]);
            }

            wrapper.appendChild(canvas);
            wrapper.appendChild(linksLayer);
            container.appendChild(wrapper);

            updateSlideNumber();
            updateThumbnails();
            scrollToActiveThumbnail();

         } catch (error) {
            console.error('Error rendering page:', error);
            container.innerHTML = '<p>Error loading slide. Please try again.</p>';
         }
      }

      const additionalStyles = document.createElement('style');
      additionalStyles.textContent = `
            .current-slide {
               width: 100%;
               height: 100%;
               display: flex;
               justify-content: center;
               align-items: center;
               position: relative;
            }

            .current-slide > div {
               display: flex;
               justify-content: center;
               align-items: center;
               max-width: 100%;
               max-height: 100%;
            }

            .current-slide canvas {
               max-width: 100%;
               max-height: 100%;
               object-fit: contain;
            }

            /* Styles for fullscreen mode */
            :fullscreen .presentation-container {
               background-color: white !important;
               width: 100vw !important;
               height: 100vh !important;
               display: flex;
               justify-content: center;
               align-items: center;
            }

            :fullscreen .current-slide canvas {
               max-width: 100vw !important;
               max-height: 100vh !important;
               object-fit: contain;
            }
         `;
      document.head.appendChild(additionalStyles);

      let resizeTimeout;
      window.addEventListener('resize', () => {
         clearTimeout(resizeTimeout);
         resizeTimeout = setTimeout(() => {
            showPage(currentPageNum);
         }, 250);
      });

      function updateThumbnails() {
         document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.toggle('active', parseInt(thumb.dataset.page) === currentPageNum);
         });
      }

      function updateSlideNumber() {
         document.getElementById('slideNumber').textContent =
            `${currentPageNum} / ${pdfDocument.numPages}`;
      }

      function prevSlide() {
         showPage(currentPageNum - 1);
      }

      function nextSlide() {
         showPage(currentPageNum + 1);
      }

      function toggleFullscreen() {
         if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().then(() => {
               isFullscreen = true;
               // Rerender the current page with higher quality
               setTimeout(() => {
                  showPage(currentPageNum);
               }, 100); // Small delay to ensure fullscreen is complete
            }).catch(err => {
               console.log('Error attempting to enable fullscreen:', err);
            });
         } else {
            document.exitFullscreen().then(() => {
               isFullscreen = false;
               // Rerender with normal quality
               setTimeout(() => {
                  showPage(currentPageNum);
               }, 100); // Small delay to ensure fullscreen exit is complete
            }).catch(err => {
               console.log('Error attempting to exit fullscreen:', err);
            });
         }
      }

      // Listen for fullscreen change events
      document.addEventListener('fullscreenchange', () => {
         isFullscreen = !!document.fullscreenElement;
         // Rerender the current page with appropriate quality
         showPage(currentPageNum);
      });

      function scrollToActiveThumbnail() {
         const activeThumbnail = document.querySelector('.thumbnail.active');
         if (activeThumbnail) {
            const container = document.getElementById('thumbnailContainer');

            const containerRect = container.getBoundingClientRect();
            const thumbnailRect = activeThumbnail.getBoundingClientRect();

            const scrollPosition = (thumbnailRect.top + container.scrollTop) -
               (containerRect.height / 2) + (thumbnailRect.height / 2);

            container.scrollTo({
               top: scrollPosition,
               behavior: 'smooth'
            });
         }
      }

      document.addEventListener('keydown', (e) => {
         switch (e.key) {
            case 'ArrowLeft':
               prevSlide();
               break;
            case 'ArrowRight':
               nextSlide();
               break;
            case 'f':
               toggleFullscreen();
               break;
         }
      });
   </script>
</body>

</html>