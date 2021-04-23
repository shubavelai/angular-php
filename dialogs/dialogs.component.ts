import { Component, Inject, Input, OnInit, ViewChild, ElementRef } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from '@angular/material/dialog';
import { FormBuilder, Validators, FormsModule, FormGroup, FormControl,ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldControl, FloatLabelType } from '@angular/material/form-field';
import { ApiService } from './../api/api.service';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { Observable,BehaviorSubject } from 'rxjs';

//import {RxwebValidators} from {@rxweb/RxwebValidators}
@Component({
  selector: 'app-dialogs',
  templateUrl: './dialogs.component.html',
  styleUrls: ['./dialogs.component.scss']
})

export class DialogsComponent implements OnInit {
  data; isload; list;
  childForm: FormGroup;
  editUserForm: FormGroup;
  //SERVER_URL = "api/upload.php";
  imageName;
  dataimage:any;
  hiddenUpload:any;
   @ViewChild('fileInput')
   set fileInput(val: ElementRef) {}
  //fileAttr = 'Choose File';

  constructor(private formBuilder: FormBuilder, private service: ApiService,public dialog: MatDialog,
    private dialogRef: MatDialogRef<DialogsComponent>, @Inject(MAT_DIALOG_DATA) data, public router:Router,private httpClient: HttpClient)
  {
    this.data = data
    this.editUserForm = this.formBuilder.group({
      id: [''],
      name: ['', Validators.required],
      mail: ['', Validators.required],
      mobile: ['', Validators.required],
      address1: ['', Validators.required],
      address2: ['', Validators.required],
      address3: ['',Validators.required],
      blood: ['', Validators.required],
      gender: ['', Validators.required],
      dp: ['']
    });
  
    this.childForm = this.formBuilder.group({
      parentId: [this.data.parentId, Validators.required],
      name: ['', Validators.required],
      blood: ['', Validators.required],
      gender: ['', Validators.required],
      dob: ['', Validators.required],
      dp: ['']
    })
    if (this.data.todo == "editUser") {
      this.isload = true;
      this.service.getData("filterData",this.data).subscribe(data => {
        this.list = data
        this.dataimage = (this.list.dp !='') ? this.service.uploadFolder+ this.list.dp:'';
        this.hiddenUpload=false;
        this.isload = false
      })
    }
    
  }

  setRequired() {
		return [Validators.required];
	}

  ngOnInit(): void {
    this.isload = false
    this.list = ""
  }
  closeDialog() {
    this.dialogRef.close('close');
    this.isload = true
  }

  async updateUser() {
    this.isload = true;
    /*if(this.hiddenUpload == true){       
        this.generateImagename(this.editUserForm.value['dp']);   
        this.httpClient.post<any>(this.service.uploadUrl, {"fileSource":this.dataimage,"name":this.imageName}).subscribe(
            (res) => console.log(res.data),
            (err) => console.log(err)
        );
        this.editUserForm.value['dp'] =this.imageName;
    }*/
    if(this.hiddenUpload == true){
      this.editUserForm.value['dp'] = this.dataimage;
    }else{
      this.editUserForm.value['dp'] = "";
    }
    //this.editUserForm.value['dp'] = this.imageName;
    this.service.getData("updateUser",this.editUserForm.value).subscribe(data => {
        this.dialogRef.close(data)
        this.isload = false
    })
  }

  async remove(data) {
    this.service.getData(data, this.data).subscribe(data => {
      this.dialogRef.close(data)
      this.isload = false
    })
    this.isload = true
  }

  async reload(data){
    this.dialogRef.close();
    this.router.navigate([data]);
  }

  async addUser() {
    this.isload = true

    if(this.hiddenUpload == true){
      this.editUserForm.value['dp'] = this.dataimage;
    }else{
      this.editUserForm.value['dp'] = "";
    }

    this.service.getData("addUser", this.editUserForm.value).subscribe(data => {
      this.dialogRef.close(data)
      this.isload = false
    })
  }

  async addChild(){
    this.isload = true
    this.service.getData("addChild", this.childForm.value).subscribe(data => {
      console.log(data)
      this.dialogRef.close(data)
      this.isload = false
    })
  }

  uploadFileEvt(imgFile: any) {
    if (imgFile.target.files && imgFile.target.files[0]) {
      /*Array.from(imgFile.target.files).forEach((file: File) => {
        this.list.dp += file.name ;
      });
*/  
      if(this.list && this.list.dp){
        this.list.dp = imgFile.target.files[0].name;
      }

      this.editUserForm.value['dp'] = imgFile.target.files[0].name;
      // HTML5 FileReader API
      let reader = new FileReader();
      /*reader.onload = (e: any) => {
        let image = new Image();
        image.src = e.target.result;
        image.onload = rs => {
          let imgBase64Path = e.target.result;
          this.dataimage = imgBase64Path;
        };
      };*/

    reader.readAsDataURL(imgFile.target.files[0]);
    reader.onload = () => {
      this.dataimage = reader.result;
    };
    //console.log(this.dataimage);
  
      /*reader.onload = (e: any) => {
        let image = new Image();
        image.src = imgFile.target.result;
        image.onload = rs => {
          let imgBase64Path = e.target.result;
          this.dataimage = imgBase64Path;
        };
      };*/
      //this.list.dp = this.dataimage;
      //reader.readAsDataURL(imgFile.target.files[0]);
      
      // Reset if duplicate image uploaded again
      //this.fileInput.nativeElement.value = "";
      this.hiddenUpload = true;
    } else {
      this.list.dp = 'Choose File';
    }
  }

  generateImagename(img:any) {
    this.imageName = img.split('.').slice(0, -1).join('.');
        this.imageName = this.imageName.replace(/[\. ,:()]+/g, "-");
        this.imageName = this.imageName+"-"+new Date().valueOf()+".png";
        this.imageName = this.imageName.replace(/--+/g, "-");
      
  }


}