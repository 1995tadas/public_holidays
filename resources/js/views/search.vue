<template>
    <div class="d-flex justify-content-center align-items-center flex-column bg-primary w-100 my-2 p-4 rounded"
         @keydown.enter="validateData">
        <h1 class="text-center">Public holidays search</h1>
        <div class="row w-100 text-center">
            <label v-if=!countries.error class="col" for="countries">Available countries</label>
            <label class="col" v-show="regions.length" for="regions">Available regions</label>
            <label class="col" v-show="Object.keys(yearsRange).length" for="years">Possible years</label>
        </div>
        <div v-if=!countries.error class="row w-100">
            <select id="countries" class="col p-2" @change="onChange()"
                    v-model="countryCode" name="year" required>
                <option selected="selected" disabled="disabled">Select a country</option>
                <option v-for="country in countries" :value="country.countryCode">{{ country.fullName }}</option>
            </select>
            <select v-show="regions.length" id="regions" class="col p-2 ml-1"
                    v-model="region" name="region" required>
                <option selected="selected" disabled="disabled">Select a region</option>
                <option v-for="region in regions" :value="region">{{ region }}</option>
            </select>
            <input id="years" class="col p-2 ml-1" type="number"
                   v-show="Object.keys(yearsRange).length"
                   :min="yearsRange.fromDate"
                   :max="yearsRange.toDate"
                   :placeholder="yearsRange.fromDate+' - '+ yearsRange.toDate"
                   v-model="year" required>
        </div>
        <div v-if=!countries.error class="row mt-4 w-50">
            <button :disabled="!countryCode" type="button" @click.prevent="validateData"
                    class="btn btn-secondary w-100">Ieškoti
            </button>
        </div>
        <div class="alert alert-danger mt-4" v-show="Object.keys(errors).length">
            <div v-for="error in errors">{{ error[0] }}</div>
        </div>
        <ul class="search-list" v-show="Object.keys(publicHolidays).length">
            <li class="search-title">Public holidays</li>
            <template v-for="(month, index) in publicHolidays">
                <li class="search-item"><strong>|{{ index }}|</strong></li>
                <li class="search-item" v-for="holiday in month">{{ holiday }},</li>
            </template>
            <li class="search-title">Total amount</li>
            <li>{{ total }}</li>
            <li class="search-title">Today</li>
            <li>{{ today }}</li>
            <li class="search-title">The maximum number of free days in a row</li>
            <li>{{ streak }}</li>
        </ul>
    </div>
</template>
<script>
export default {
    props: {
        countries: {
            type: [Object, Array],
            required: true
        },
        showRoute: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            countryCode: '',
            yearsRange: [],
            regions: [],
            region: '',
            year: '',
            errors: [],
            publicHolidays: [],
            total: 0,
            streak: 0,
            today: ''
        }
    },
    methods: {
        onChange() {
            this.getYears(this.countryCode);
        },
        getYears(code) {
            for (const country of this.countries) {
                if (code === country.countryCode) {
                    this.yearsRange['fromDate'] = country.fromDate.year;
                    this.yearsRange['toDate'] = country.toDate.year;
                    this.getRegion(country);
                    break;
                }
            }
        },
        getRegion(country) {
            if (country.regions.length !== 0) {
                this.regions = country.regions;
            } else {
                this.regions = [];
                this.region = '';
            }
        },
        validateData() {
            if (!(this.countryCode && this.year)) {
                this.errors = [['Country and year are required !']];
            } else if (this.regions.length && !this.region) {
                this.errors = [['Region is required !']];
            } else if (this.year < this.yearsRange.fromDate || this.year > this.yearsRange.toDate) {
                this.errors = [['Years\ "' + this.year + '" can\'t be lower than ' + this.yearsRange.fromDate + ' or higher than ' + this.yearsRange.toDate]];
            } else {
                this.errors = [];
                this.getHolidays();
            }
        },
        getHolidays() {
            axios.get(this.showRoute, {
                params: {
                    year: this.year,
                    country: this.countryCode,
                    region: this.region
                }
            }).then((response) => {
                    if (!response.data.error) {
                        this.publicHolidays = response.data.holidays;
                        this.total = response.data.total;
                        this.streak = response.data.longestStreak;
                        this.today = response.data.today;
                    } else {
                        this.errors = [[response.data.error]]
                    }
                }
            ).catch((response) => {
                    console.log(this.errors)
                }
            )
        }
    },
    created() {
        if (this.countries.error) {
            this.errors = [[this.countries.error]];
        }
    }
};
</script>
