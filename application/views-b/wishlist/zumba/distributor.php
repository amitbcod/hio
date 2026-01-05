<?php $country_code = $this->session->userdata('ip_country'); ?>

<?php if(!isset($_COOKIE['acceptedDistributorCookie'])){
	$result = distributor_country($country_code);
?>
	<div class="tw-fixed tw-inset-0" style="background-color: hsla(235, 5%, 50%, .5); z-index: 10000; display:none; overflow-y: scroll" x-ref="theModal2" x-data="{distributorModal:true}" x-show.transition="distributorModal" >
		<div class="tw-flex tw-flex-col tw-h-full">
			<!-- distributor-modal background -->
			<div class="tw-relative tw-flex-1 tw-z-10 tw-justify-center tw-items-center tw-w-full sm:tw-flex">
				<!-- modal -->
				<div class="" @click.away="hideModal()">
					<div class="tw-flex tw-flex-col md:tw-flex-row tw-relative modal-lg">
					<button type="button" class="tw-absolute tw-top-2 tw-right-2 tw-z-30 tw-text-white" @click="setCookie('acceptedDistributorCookie', 'closed',30); distributorModal = false; ">
						<svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6 tw-fill-current" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
						</svg>
					</button>
					<div class="tw-flex tw-items-center tw-h-3/30 tw-bg-cover" style="background-image: url('https://fbc-shop1.s3.amazonaws.com/pattern-bg_LandingPageNewslettersubscription_032922.png')">
						<div class="tw-z-10 tw-px-5 tw-py-16 sm:tw-py-10 tw-w-full sm:tw-px-10 tw-text-left">
							<h3 class="tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-3xl tw-mb-4"><?=lang('distributor_title'). ' ' . $result[0]; ?></h3>
							<h3 class="tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-2xl tw-mb-4"><?=lang('distributor_subtitile'); ?></h3>
							<div class="tw-mt-2 tw-leading-tight tw-mb-4">
								<p class="tw-text-left tw-mb-2 tw-text-white"><a class="tw-text-zumbahotlime" href= <?php echo $result[1]; ?>>
								<button type="submit" class="tw-bg-zumbahotlime tw-transition tw-px-4 tw-py-2  hover:tw-bg-zumbapurple hover:tw-text-white tw-text-black"> <?=lang('distributor_btn')?></button>  &nbsp;</a><?=lang('distributor_text'); ?>
								</p>
							</div>
							<div class="tw-mt-4 tw-text-white">
								<p><?=lang('continue_shopping'); ?><a class="tw-text-zumbahotlime" href="<?php echo BASE_URL; ?>" id="zumba_link" @click="setCookie('acceptedDistributorCookie', 'closed',30); distributorModal = false; "> zumbawear.eu</a></p>
								<p class="tw-text-sm"><?=lang('distributor_note').' '. $result[0].'.'; ?></p>
							</div>

							<?php if($country_code == 'PL') {?>
								<!-- poland -->
								<div class="tw-mt-4 tw-border-t-[0.5px]">
									<h3 class="tw-mt-4 tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-3xl tw-mb-4">Hej, wygląda na to, że przeglądasz naszą stronę z Polski.</h3>
									<h3 class="tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-2xl tw-mb-4">Mamy dystrybutora w Twoim kraju.</h3>
									<div class="tw-mt-2 tw-leading-tight tw-mb-4">
										<p class="tw-text-left tw-mb-2 tw-text-white"><a class="tw-text-zumbahotlime" href="https://sklep.zumbasklep.pl/">
										<button type="submit" class="tw-bg-zumbahotlime tw-transition tw-px-4 tw-py-2  hover:tw-bg-zumbapurple hover:tw-text-white tw-text-black">Kliknij tutaj</button>  &nbsp;</a> aby przejść na jego stronę
										</p>
									</div>
									<div class="tw-mt-4 tw-text-white">
										<p>Kontynuuj zakupy na <a class="tw-text-zumbahotlime" href="<?php echo BASE_URL; ?>" id="zumba_link" @click="setCookie('acceptedDistributorCookie', 'closed',30); distributorModal = false; "> zumbawear.eu</a></p>
										<p class="tw-text-sm">Informujemy, że obecnie nie prowadzimy wysyłek do Polski.</p>
									</div>
								</div>
							<?php }
							elseif($country_code == 'TR') {?>
								<!-- Turkey -->
								<div class="tw-mt-4 tw-border-t-[0.5px]">
									<h3 class="tw-mt-4 tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-3xl tw-mb-4">Hey,görünüşe göre Türkiye’den bir siteye gözatıyorsun.</h3>
									<h3 class="tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-2xl tw-mb-4">Ülkenizde bir distribütörümüz var.</h3>
									<div class="tw-mt-2 tw-leading-tight tw-mb-4">
										<p class="tw-text-left tw-mb-2 tw-text-white"><a class="tw-text-zumbahotlime" href="https://www.zumbawearturkiye.com/">
										<button type="submit" class="tw-bg-zumbahotlime tw-transition tw-px-4 tw-py-2  hover:tw-bg-zumbapurple hover:tw-text-white tw-text-black">Click here</button>  &nbsp;</a> websitesine gitmek için tıklayın.
										</p>
									</div>
									<div class="tw-mt-4 tw-text-white">
										<p>alışverişinize devam edin <a class="tw-text-zumbahotlime" href="<?php echo BASE_URL; ?>" id="zumba_link" @click="setCookie('acceptedDistributorCookie', 'closed',30); distributorModal = false; "> zumbawear.eu</a></p>
										<p class="tw-text-sm">Lütfen dikkat,şu anda Türkiye’ye gönderim yapmıyoruz.</p>
									</div>
								</div>
							<?php }
							elseif($country_code == 'HU') {?>
								<!-- Hungary -->
								<div class="tw-mt-4 tw-border-t-[0.5px]">
									<h3 class="tw-mt-4 tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-3xl tw-mb-4">Hello, úgy tűnik, hogy az oldalunkat Magyarországról böngészed, ahol van egy hivatalos disztribútorunk.</h3>
									<div class="tw-mt-2 tw-leading-tight tw-mb-4">
										<p class="tw-text-left tw-mb-2 tw-text-white"><a class="tw-text-zumbahotlime" href="https://www.zumba-shop.hu/">
										<button type="submit" class="tw-bg-zumbahotlime tw-transition tw-px-4 tw-py-2  hover:tw-bg-zumbapurple hover:tw-text-white tw-text-black">Kattints ide</button>  &nbsp;</a> és máris átirányítunk a weboldalukra.
										</p>
									</div>
									<div class="tw-mt-4 tw-text-white">
										<p>Folytatom a vásárlást a <a class="tw-text-zumbahotlime" href="<?php echo BASE_URL; ?>" id="zumba_link" @click="setCookie('acceptedDistributorCookie', 'closed',30); distributorModal = false; "> zumbawear.eu</a> oldalon.</p>
										<p class="tw-text-sm">Felhívjuk figyelmedet, hogy jelenleg nem szállítunk Magyarországra.</p>
									</div>
								</div>
							<?php } ?>

						</div>

					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php  } ?>
