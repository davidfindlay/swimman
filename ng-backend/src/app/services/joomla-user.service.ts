import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {PagedData} from '../models/paged-data';
import {JoomlaUser} from '../models/joomla-user';
import {Page} from '../models/page';
import {Observable} from 'rxjs';


@Injectable({
    providedIn: 'root'
})
export class JoomlaUserService {

    joomla_users = new PagedData<JoomlaUser>();

    constructor(private http: HttpClient) {
    }



}
