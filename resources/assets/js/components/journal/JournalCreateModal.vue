<template>
    <modal
        :show="show"
        :callback="create"
        :ok-text="'Create'"
        @close="$emit('close')"
    >
        <div slot="heading">
            Create New Journal
        </div>

        <div slot="body">
            <template v-if="dates.length > 0">
                <form @submit.prevent="callback">
                    <select class="select" v-model="journal.publish_date">
                        <option v-for="date of dates" :value="date">
                            {{ date }}
                        </option>
                    </select>
                    <textarea class="textarea" v-model="journal.contents"></textarea>
                    <textarea class="textarea" v-model="journal.events"></textarea>
                </form>
            </template>
        </div>
    </modal>
</template>

<script>
    import Modal from '../Modal.vue';

    export default {
        props: ['show'],

        components: {
            Modal,
        },

        /**
         * The component's data.
         */
        data() {
            return {
                dates: [],
                isLoading: false,

                journal: {
                    errors: [],
                    publish_date: '',
                    contents: '',
                    events: '',
                },
            };
        },

        methods: {
            /**
             * Get the list of dates without entries for the user.
             */
            getDatesWithoutEntries() {
                this.$http.get('/api/journals/dates_without_entry')
                        .then(({body}) => this.dates = body);
            },

            /**
             * Create the journal entry.
             */
            create() {
                this.isLoading = true;
                this.$http.post('/api/journals', this.journal)
                        .then(() => {
                            this.reset();
                            this.$emit('close');
                            eventBus.$emit('refresh');
                        })
                        .catch(({body}) => {
                            this.journal.errors = body.error.message;
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
            },

            /**
             * Reset the form.
             */
            reset() {
                this.dates = [];
                this.isLoading = false;

                this.journal.errors = [];
                this.journal.publish_date = '';
                this.journal.contents = '';
                this.journal.events = '';
            },
        },

        watch: {
            show() {
                if (this.show) {
                    this.getDatesWithoutEntries();
                } else {
                    this.reset();
                }
            },
        },
    }
</script>