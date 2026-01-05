<div class="tw-fixed tw-inset-0" style="background-color: hsla(235, 5%, 50%, .5); z-index: 10000; display:none; overflow-y: scroll" x-ref="theModal" x-show="showModal" x-data="signupComponent()">
    <div class="tw-flex tw-flex-col tw-h-full">
        <!-- signup-modal background -->
        <div class="tw-relative tw-flex-1 tw-z-10 tw-justify-center tw-items-center tw-w-full sm:tw-flex">
            <!-- modal -->
            <div class="tw-w-full tw-bg-[#570073] tw-max-w-4xl tw-flex tw-text-white sm:tw-mx-6" @click.away="hideModal()">
                <div class="tw-h-auto md:tw-col-span-4 tw-relative">
                    <button type="button" class="tw-absolute md:tw-hidden tw-top-2 tw-right-2 tw-z-30 tw-text-white" @click="hideModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6 tw-fill-current" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="tw-flex tw-items-center tw-h-3/5 tw-bg-cover" style="background-image: url('https://fbc-shop1.s3.amazonaws.com/pattern-bg_LandingPageNewslettersubscription_032922.png')">
                        <div class="tw-z-10 tw-px-5 tw-py-16 sm:tw-py-10 tw-w-full sm:tw-px-10 justify-center-center">
                            <h3 class="tw-text-zumbahotlime tw-font-bold tw-text-2xl md:tw-text-3xl">
                                <?=lang('title')?>
                            </h3>
                            <p class="tw-pt-6 tw-pb-5 tw-mt-4 tw-text-xl tw-font-light"><?=lang('cta-1')?></p>

                            <div class="tw-max-w-full">
                                <form action="" x-show="!submitted" id="signup-form" method="POST">
                                    <div class="sm:tw-flex tw-w-full">
                                        <label for="email" class="tw-sr-only"><?=lang('email-placeholder')?></label>
                                        <input type="email" placeholder="<?=lang('email-placeholder')?>" autocomplete="email" x-model="email" id="email" class="tw-text-lg tw-px-4 tw-py-2 tw-w-full tw-font-light tw-text-black tw-flex-1 focus:tw-ring-zumbahotlime focus:tw-ring-2 focus:tw-outline-none tw-ring-offset-4 tw-ring-offset-[#570073]">
                                        <button @click.prevent="signup()" type="submit"
                                                class="sm:tw-ml-2 tw-bg-[#FF5E63] tw-py-2 tw-px-6 tw-font-light tw-uppercase tw-text-lg tw-w-full sm:tw-w-auto tw-mt-2 sm:tw-mt-0 hover:tw-bg-[#D43361] focus:tw-bg-[#D43361] tw-transition focus:tw-ring-zumbahotlime focus:tw-ring-2 focus:tw-outline-none tw-ring-offset-4 tw-ring-offset-[#570073]">
                                            <?=lang('button-1')?>
                                        </button>
                                    </div>
                                </form>
                                <div class="tw-py-1 tw-min-h-[72px] sm:min-h-max tw-flex tw-items-center sm:tw-flex-none tw-text-lg tw-text-zumbahotlime" x-show="submitted" style="display: none">
                                    <?=lang('subscribe-confirmation')?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-h-2/5 tw-p-5 sm:tw-p-10 tw-border-t-[0.5px] tw-border-zumbahotlime">
                        <div class="tw-flex tw-flex-col">
                            <p class="tw-text-zumbahotlime tw-text-lg tw-font-bold tw-uppercase"><?=lang('privacy-title')?></p>
                            <p class="tw-font-light tw-mt-2">
                                <?=lang('privacy-paragraph-1')?>
                            </p>
                            <p class="tw-font-light tw-mt-4">
                                <?=lang('privacy-paragraph-2')?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="tw-hidden tw-w-96 md:tw-block tw-bg-cover tw-bg-right tw-relative" style="background-image: url('https://fbc-shop1.s3.amazonaws.com/cropped_image.png')">
                    <button type="button" class="tw-absolute tw-top-2 tw-right-2 tw-z-30 tw-text-white" @click="hideModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6 tw-fill-current" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function signupComponent(){
        return {
            showModal: false,
            submitted : false,
            email: null,
            init: function(){
                const params = new Proxy(new URLSearchParams(window.location.search), {
                    get: (searchParams, prop) => searchParams.get(prop),
                });

                if(params.utm_source === 'Iterable'){
                    this.showModal = true;
                } else {
					this.showModal = false;
                    // this.showModal = localStorage.getItem('showModal') !== 'false';
                }

            },
            hideModal: function(){
                this.$refs.theModal.style = "display: none;";
                this.showModal = false;
                localStorage.setItem('showModal', 'false');
            },
            signup : function(){
                fetch('/zumba/mailjet-signup', {
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json, text-plain, */*",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({
                        email: this.email,
                    })
                })
            .then(response => response.json())
            .then((response) => {
                if(response.message === 'success'){
                    this.submitted = true;
                    setTimeout(() => this.hideModal(), 1500);
                    window.fathom.trackGoal('CLJOAXCC', 0);
                } else {
                    window.fathom.trackGoal('R4FT8MBA', 0);
                }
            });
            }
        }
    }
</script>
