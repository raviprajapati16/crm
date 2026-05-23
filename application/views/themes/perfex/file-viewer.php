<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document Viewer | <?= get_option('brandname') ?></title>
	<?php
	theme_style_clients_area_head();
	?>
	<style>
		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			margin: 0;
			padding: 0;
			background-color: #f5f5f5;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}

		.header {
			background: linear-gradient(90deg, #0d572e 35%, #fdc900 100%);
			color: white;
			padding: 15px 20px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}

		.header h1 {
			margin: 0;
			font-size: 24px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.controls {
			background-color: #fff;
			padding: 10px 20px;
			border-bottom: 1px solid #ddd;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
			position: sticky;
			top: 0;
			z-index: 100;
			flex-wrap: wrap;
			gap: 10px;
		}

		.btn {
			background-color: #0d572e;
			color: white;
			border: none;
			padding: 8px 15px;
			margin: 0 5px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			transition: background-color 0.2s;
			min-width: 40px;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.btn:hover {
			background-color: #0a4023;
		}

		.btn:disabled {
			background-color: #cccccc;
			cursor: not-allowed;
		}

		.page-info {
			margin: 0 15px;
			font-size: 14px;
			white-space: nowrap;
		}

		.viewer-container {
			max-width: 1000px;
			margin: 20px auto;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			padding: 20px;
			position: relative;
			width: calc(100% - 40px);
		}

		#document-container {
			display: flex;
			flex-direction: column;
			align-items: center;
			max-height: 80vh;
			overflow-y: auto;
			padding: 10px;
			position: relative;
			width: 100%;
		}

		.document-page {
			border: 1px solid #ddd;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			margin-bottom: 15px;
			width: 100%;
			position: relative;
			background-color: white;
		}

		.document-page canvas {
			display: block;
			width: 100%;
			height: auto;
		}

		.document-page img {
			display: block;
			width: 100%;
			height: auto;
			pointer-events: none;
		}

		.loader {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 30px;
			width: 100%;
		}

		.spinner {
			border: 5px solid #f3f3f3;
			border-top: 5px solid #0d572e;
			border-radius: 50%;
			width: 40px;
			height: 40px;
			animation: spin 1s linear infinite;
			margin-bottom: 15px;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		.zoom-controls {
			display: flex;
			align-items: center;
			margin-left: 10px;
		}

		.page-marker {
			background-color: rgba(13, 87, 46, 0.1);
			color: #0d572e;
			padding: 2px 8px;
			border-radius: 3px;
			font-size: 12px;
			position: absolute;
			top: 10px;
			right: 10px;
		}

		.control-group {
			display: flex;
			align-items: center;
			justify-content: center;
			flex-wrap: wrap;
			gap: 10px;
		}

		@media (max-width: 768px) {
			.header h1 {
				font-size: 20px;
			}

			.viewer-container {
				margin: 10px auto;
				padding: 10px;
			}

			.controls {
				padding: 10px;
				flex-direction: column;
				align-items: center;
			}

			.control-group {
				width: 100%;
				justify-content: center;
				margin: 5px 0;
			}

			.zoom-controls {
				margin-left: 0;
				margin-top: 5px;
			}

			.page-info {
				margin: 5px;
			}

			.btn {
				margin: 3px;
				padding: 8px 12px;
			}

			#document-container {
				padding: 5px;
			}
		}

		@media (max-width: 480px) {
			.header h1 {
				font-size: 18px;
			}

			.viewer-container {
				padding: 8px;
				margin: 8px auto;
			}

			.btn {
				padding: 6px 10px;
				font-size: 13px;
			}

			.page-info {
				font-size: 13px;
			}

			.document-page {
				margin-bottom: 10px;
			}
		}

		.d-none {
			display: none;
		}
	</style>
</head>

<body class="file-viewer">
	<div class="header">
		<h1>Document Viewer</h1>
	</div>
	<div class="controls">
		<div class="control-group <?= (strpos($file_type, "image") !== false) ? "d-none" : "" ?>">
			<span class="page-info">Total Pages: <span id="total-pages">0</span></span>
		</div>
		<div class="control-group <?= (strpos($file_type, "image") !== false) ? "d-none" : "" ?>">
			<div class="zoom-controls">
				<button id="zoom-out" class="btn">-</button>
				<span class="page-info"><span id="zoom-level">100</span>%</span>
				<button id="zoom-in" class="btn">+</button>
			</div>
		</div>
		<?php
		if (has_permission('attachments', '', 'download')) {
		?>
			<a href="<?= site_url("download/doc_download?path=" . $path)  ?>" target="_blank" class="btn btn-primary" style="text-decoration: none;">Download</a>
		<?php
		}
		?>
	</div>

	<div class="viewer-container">
		<div id="document-container">
			<div class="loader">
				<div class="spinner"></div>
				<p>Loading document...</p>
			</div>
		</div>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
	<script>
		document.addEventListener('contextmenu', event => event.preventDefault());
		document.addEventListener('keydown', function(event) {
			if ((event.ctrlKey && (event.key === 'p' || event.key === 'P' || event.key === 's' || event.key === 'S')) ||
				(event.key === 'PrintScreen')) {
				event.preventDefault();
			}
		});

		pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

		let currentPdf = null;
		let totalPages = 0;
		let zoomLevel = 100;
		let pixelRatio = window.devicePixelRatio || 1;

		const documentContainer = document.getElementById('document-container');
		const totalPagesSpan = document.getElementById('total-pages');
		const zoomInButton = document.getElementById('zoom-in');
		const zoomOutButton = document.getElementById('zoom-out');
		const zoomLevelSpan = document.getElementById('zoom-level');

		zoomInButton.addEventListener('click', () => {
			if (zoomLevel < 200) {
				zoomLevel += 25;
				zoomLevelSpan.textContent = zoomLevel;
				updateZoom();
			}
		});

		zoomOutButton.addEventListener('click', () => {
			if (zoomLevel > 50) {
				zoomLevel -= 25;
				zoomLevelSpan.textContent = zoomLevel;
				updateZoom();
			}
		});

		const fileContent = "<?= $file_content ?>";
		const fileType = "<?= $file_type ?>";
		const fileName = "<?= $file_name ?>";

		function setInitialZoomLevel() {
			const viewportWidth = window.innerWidth;
			if (viewportWidth < 480) {
				zoomLevel = 75;
			} else if (viewportWidth < 768) {
				zoomLevel = 85;
			} else {
				zoomLevel = 100;
			}
			zoomLevelSpan.textContent = zoomLevel;
		}

		function getFileType(mimeType, name) {
			if (mimeType.includes('pdf')) {
				return 'pdf';
			} else if (mimeType.includes('image')) {
				return 'image';
			} else {
				const extension = name.split('.').pop().toLowerCase();
				if (['pdf'].includes(extension)) {
					return 'pdf';
				} else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
					return 'image';
				}
				return 'unknown';
			}
		}

		function displayImage(base64Content) {
			documentContainer.innerHTML = '';

			const pageDiv = document.createElement('div');
			pageDiv.classList.add('document-page');

			const img = document.createElement('img');
			img.src = "data:image/*;base64," + base64Content;
			img.alt = "Document Image";
			img.style.width = '100%';

			img.onload = function() {
				totalPages = 1;
				totalPagesSpan.textContent = totalPages;
			};

			img.onerror = function() {
				documentContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">Error loading image. Please try again later.</div>';
			};

			pageDiv.appendChild(img);
			documentContainer.appendChild(pageDiv);
		}

		async function loadPDFFromBase64(base64Content) {
			try {
				documentContainer.innerHTML = '<div class="loader"><div class="spinner"></div><p>Loading document...</p></div>';

				const binaryString = atob(base64Content);
				const bytes = new Uint8Array(binaryString.length);
				for (let i = 0; i < binaryString.length; i++) {
					bytes[i] = binaryString.charCodeAt(i);
				}

				const loadingTask = pdfjsLib.getDocument({
					data: bytes
				});
				currentPdf = await loadingTask.promise;
				totalPages = currentPdf.numPages;
				totalPagesSpan.textContent = totalPages;

				documentContainer.innerHTML = '';

				for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
					await renderPdfPage(pageNum);
				}

			} catch (error) {
				console.error('Error loading PDF:', error);
				documentContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">Error loading PDF document. Please try again later.</div>';
			}
		}

		async function renderPdfPage(pageNumber) {
			try {
				const page = await currentPdf.getPage(pageNumber);

				// Get container width to set as base width for the PDF page
				const containerWidth = documentContainer.clientWidth;

				// Get the original viewport
				const originalViewport = page.getViewport({
					scale: 1
				});

				// Calculate scale to fit container width
				const containerScale = containerWidth / originalViewport.width;

				// Apply zoom level
				const scale = (containerScale * zoomLevel) / 100;

				// Create viewport with the calculated scale
				const viewport = page.getViewport({
					scale
				});

				// Create page div
				const pageDiv = document.createElement('div');
				pageDiv.classList.add('document-page');
				pageDiv.style.width = '100%';

				// Create canvas with high DPI support for better quality
				const canvas = document.createElement('canvas');
				const context = canvas.getContext('2d', {
					alpha: false
				});

				// Set canvas dimensions with pixel ratio for high DPI displays
				const outputScale = pixelRatio;
				canvas.width = Math.floor(viewport.width * outputScale);
				canvas.height = Math.floor(viewport.height * outputScale);

				// Scale canvas CSS size to match viewport
				canvas.style.width = Math.floor(viewport.width) + 'px';
				canvas.style.height = Math.floor(viewport.height) + 'px';

				const pageMarker = document.createElement('div');
				pageMarker.classList.add('page-marker');
				pageMarker.textContent = `Page ${pageNumber}`;
				pageDiv.appendChild(pageMarker);
				pageDiv.appendChild(canvas);
				documentContainer.appendChild(pageDiv);

				// Set transform for high DPI rendering
				const transform = outputScale !== 1 ?
					[outputScale, 0, 0, outputScale, 0, 0] :
					null;

				// Render with high quality settings
				const renderContext = {
					canvasContext: context,
					viewport: viewport,
					transform: transform,
					enableWebGL: true,
					renderInteractiveForms: true,
					textLayer: true
				};

				await page.render(renderContext).promise;

			} catch (error) {
				console.error('Error rendering page:', error);
				const errorDiv = document.createElement('div');
				errorDiv.style = 'padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;';
				errorDiv.textContent = `Error rendering page ${pageNumber}. Please try again.`;
				documentContainer.appendChild(errorDiv);
			}
		}

		function updateZoom() {
			if (currentPdf) {
				documentContainer.innerHTML = '';
				for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
					renderPdfPage(pageNum);
				}
			} else {
				const pageDiv = document.querySelector('.document-page');
				if (pageDiv) {
					pageDiv.style.width = '100%';
				}
			}
		}

		function loadDocument() {
			if (!fileContent) {
				documentContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px;">No document content provided.</div>';
				return;
			}

			const fileType = getFileType("<?= $file_type ?>", fileName);

			switch (fileType) {
				case 'pdf':
					loadPDFFromBase64(fileContent);
					break;
				case 'image':
					displayImage(fileContent);
					break;
				default:
					documentContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px;">Unsupported file type.</div>';
			}
		}

		window.addEventListener('resize', function() {
			if (!this.resizeTimeout) {
				this.resizeTimeout = setTimeout(() => {
					this.resizeTimeout = null;
					pixelRatio = window.devicePixelRatio || 1;
					updateZoom();
				}, 500);
			}
		});

		document.addEventListener('DOMContentLoaded', function() {
			setInitialZoomLevel();
			pixelRatio = window.devicePixelRatio || 1;
			loadDocument();

			if ('ontouchstart' in window) {
				let touchStartX = 0;
				let touchStartY = 0;
				let initialScale = 1;

				documentContainer.addEventListener('touchstart', function(e) {
					if (e.touches.length === 2) {
						const touch1 = e.touches[0];
						const touch2 = e.touches[1];
						initialScale = Math.hypot(
							touch2.clientX - touch1.clientX,
							touch2.clientY - touch1.clientY
						);
					} else if (e.touches.length === 1) {
						touchStartX = e.touches[0].clientX;
						touchStartY = e.touches[0].clientY;
					}
				});

				documentContainer.addEventListener('touchmove', function(e) {
					if (e.touches.length === 2 && initialScale > 0) {
						const touch1 = e.touches[0];
						const touch2 = e.touches[1];
						const currentDistance = Math.hypot(
							touch2.clientX - touch1.clientX,
							touch2.clientY - touch1.clientY
						);

						if (currentDistance > initialScale * 1.2 && zoomLevel < 200) {
							zoomLevel += 25;
							zoomLevelSpan.textContent = zoomLevel;
							updateZoom();
							initialScale = currentDistance;
						} else if (currentDistance < initialScale * 0.8 && zoomLevel > 50) {
							zoomLevel -= 25;
							zoomLevelSpan.textContent = zoomLevel;
							updateZoom();
							initialScale = currentDistance;
						}

						e.preventDefault();
					}
				});
			}
		});
	</script>
</body>

</html>