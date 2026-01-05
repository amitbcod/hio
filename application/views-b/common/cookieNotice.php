<?php if(!isset($_COOKIE['acceptedCookieNotice'])): ?>
<link rel="stylesheet" type="text/css" href="<?php echo SKIN_CSS ?>tailwind.css?v=<?php echo CSSJS_VERSION; ?>" media="all" >

<section class="tw-bg-[#c3d5d6] md:tw-py-8 tw-py-4 tw-sticky tw-bottom-0 tw-w-full tw-z-10 cookiesHide" x-data="{ showCookieNotice: true }" x-show.transition="showCookieNotice" >
	<div class="md:tw-container md:tw-mx-auto md:tw-flex">
		<div class="tw-pl-4 md:tw-pl-0 w-flex-1 tw-max-h-48 md:tw-max-h-full tw-overflow-y-scroll md:tw-overflow-y-auto">
			<div class="tw-font-bold tw-text-xl tw-mb-4">We want to give you the best shopping experience.</div>
			<div class="tw-prose tw-max-w-full md:tw-pr-24">
				<p>We use cookies and other technologies to improve your shopping experience and deliver our services. Furthermore, we also use these cookies to understand how customers use our services (for example: measure website visits), and allow us to improve our website.</p>
				<p>Using these technologies, we can show you the most relevant content, including personalized advertising. In order for this to work, we collect data about our users.</p>
				<p>If you click “Accept Cookies”, you agree and allow us to share your data with third parties. This may include the processing of your data outside of Europe. If you do not agree, we will limit ourselves to the essential cookies, and you will only experience our basic content.</p>
				<p>You can find more information in our privacy policy. <a href="/page/privacypolicy">privacy policy</a>.
				</p></div>
		</div>
		<div class="tw-ml-8 tw-flex md:tw-items-center tw-mt-8 tw-space-x-4">
			<button class="tw-bg-zumbadark tw-text-white tw-font-bold tw-py-2 tw-px-4 tw-whitespace-nowrap" onclick="setCookie('acceptedCookieNotice', 'full', 1000); showCookieNotice = false;">Accept cookies</button>
			<div class="md:tw-absolute md:tw-top-2 md:tw-right-4 tw-text-gray-900"><button class="tw-py-2 tw-px-4" onclick="setCookie('acceptedCookieNotice', 'limited', 1000); showCookieNotice = false;">X</button></div>
		</div>
	</div>
</section>
<?php endif; ?>


