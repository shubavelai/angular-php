import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import * as crypto from 'crypto-js';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  data;
  secretKey = "hashalgolforarunaapp";
  private url = "http://192.168.18.34/aruna/api/apis.php";
  constructor(private http: HttpClient) { }

  serviceFunction(val1: any, val2: any) {
    this.data = { action: val1, id: val2 }
    return this.http.post(this.url, this.data)
  }

  getData(val1: any, val2: any) {
    this.data = { action: val1, id: val2 }
    return this.http.post(this.url, this.data)
  }
  encrypt(data){
    return crypto.AES.encrypt(data, this.secretKey.trim()).toString();
  }
  decrypt(data){
    return crypto.AES.decrypt(data, this.secretKey.trim()).toString(crypto.enc.Utf8);
  }
}
