import {BaseApiService} from "./BaseApiService.js";

export default class HeaderService extends BaseApiService {
    constructor() {
        super("/headers");
    }
}
